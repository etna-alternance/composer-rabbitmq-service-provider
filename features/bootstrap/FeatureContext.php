<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require __DIR__ . "/../../vendor/autoload.php";

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

use ETNA\Silex\Provider\RabbitMQ\RabbitMQServiceProvider;
use Silex\Application;

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    static private $vhosts = ["/test-behat", "/test-behat-named"];

    /**
     * @BeforeSuite
     */
    static public function createVhosts()
    {
        foreach (self::$vhosts as $vhost) {
            passthru("rabbitmqctl add_vhost {$vhost} > /dev/null");
            passthru("rabbitmqctl set_permissions -p {$vhost} guest '.*' '.*' '.*' > /dev/null");
        }
    }

    /**
     * @AfterSuite
     */
    static public function deleteVhosts()
    {
        foreach (self::$vhosts as $vhost) {
            passthru("rabbitmqctl delete_vhost {$vhost} > /dev/null");
        }
    }

    /**
     * @Given /^une application Silex$/
     */
    public function uneApplicationSilex()
    {
        $this->app = new Application();
        $this->app->register(new RabbitMQServiceProvider());
    }

    /**
     * @Given /^la configuration suivante :$/
     */
    public function laConfigurationSuivante(PyStringNode $config)
    {
        $config = json_decode($config->getRaw(), true);
        if (!$config && json_last_error()) {
            throw new PendingException("Invalid JSON");
        }
        foreach ($config as $key => $value) {
            $this->app[$key] = $value;
        }
    }

    /**
     * @Given /^\$app\["amqp\.chan"\] == \$app\["amqp\.chans"\]\["default"\]$/
     */
    public function chanEstUnAliasVersDefault()
    {
        if ($this->app["amqp.chan"] !== $this->app["amqp.chans"]["default"]) {
            throw new Exception('$app["amqp.chan"] != $app["amqp.chans"]["default"]');
        }
    }

    /**
     * @Given /^\$app\["amqp\.(\w+)"\]\["(\w+)"\] est du type ([\w\\]+)$/
     */
    public function checkClass($type, $name, $class)
    {
        if (!is_a($this->app["amqp.{$type}"][$name], $class)) {
            throw new Exception("\$app['amqp.{$type}']['{$name}'] n'est pas une instance de {$class}");
        }
    }

    /**
     * @Given /^\$app\["amqp\.(\w+)"\]\["(\w+)"\]->(\w+)\(\) == "([^"]*)"$/
     */
    public function checkGetterString($type, $name, $method, $value)
    {
        if ($this->app["amqp.{$type}"][$name]->$method() != $value) {
            $value = $this->app["amqp.{$type}"][$name]->$method();
            throw new Exception("\$app['amqp.{$type}'['{$name}']]->{$method}() = " . var_export($value, true));
        }
    }

    /**
     * @Given /^\$app\["amqp\.(\w+)"\]\["(\w+)"\]->(\w+)\(\) == (true|false)$/
     */
    public function checkGetterBoolean($type, $name, $method, $value)
    {
        if ($this->app["amqp.{$type}"][$name]->$method() != (strtolower($value) == "true")) {
            $value = $this->app["amqp.{$type}"][$name]->$method();
            throw new Exception("\$app['amqp.{$type}'['{$name}']]->{$method}() = " . var_export($value, true));
        }
    }

    /**
     * @Given /^que je bind une file sur l\'exchange "([^"]*)"$/
     */
    public function queJeBindUneFileSurLExchange($exchange)
    {
        $this->channel = $this->app["amqp.exchanges"][$exchange]->getChannel();
        $this->tmp_queue = $this->channel->queue_declare()[0];
        $this->channel->queue_bind($this->tmp_queue, $exchange);
    }

     /**
     * @Given /^que "([^"]*)" est (un exchange|une queue) réservé$/
     */
    public function queEstUnTrucReserve($name, $type)
    {
        try {
            $type = $type == 'un exchange' ? "amqp.exchanges" : "amqp.queues";
            $this->app[$type][$name]->getChannel();
        } catch (Exception $e) {
            $this->exception = $e->getMessage();
        }
    }

    /**
     * @Given /^je devrais avoir une exception "([^"]*)"$/
     */
    public function jeDevraisAvoirUneException($exception)
    {
        if ($exception != $this->exception) {
            throw new Exception("Expected: '{$exception}'; got: '{$this->exception}'");
        }
    }

    /**
     * @Given /^j\'envoie un message "(\w+)" dans l\'exchange "(\w+)"$/
     */
    public function jEnvoieUnMessage($message, $exchange)
    {
        $this->app["amqp.exchanges"][$exchange]->send($message);
    }

    /**
     * @Given /^j\'envoie un message "(\w+)" dans la file "([^"]*)"$/
     */
    public function jEnvoieUnMessageDansLaFile($message, $queue)
    {
        $this->channel = $this->app["amqp.queues"][$queue]->getChannel();
        $this->tmp_queue = $queue;
        $this->app["amqp.queues"][$queue]->send($message);
    }

    /**
     * @Given /^il doit y avoir un message "([^"]*)" dans la file( "(\w+)")?$/
     */
    public function ilDoitYAvoirUnMessageDansLaFile($message, $queue = null)
    {
        $this->channel->basic_consume($this->tmp_queue, "behat", false, false, false, false, function ($msg) use ($message) {
            $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);

            if (json_decode($msg->body) != $message) {
                throw new Exception("{$msg->body} != {$message}");
            }
        });
        $this->channel->wait();
    }

    /**
     * @Given /^je fais un listen ma callback doit être appelé (\d+) fois$/
     */
    public function jeFaisUnListen($nb)
    {
        $this->app["amqp.queues"][$this->tmp_queue]->send("__QUIT__");
        $nb++;

        $count = 0;
        $last_message = null;
        $this->app["amqp.queues"][$this->tmp_queue]->listen(function ($msg) use ($count, &$last_message) {
            $count++;
            $last_message = json_decode($msg->body);
        });
        while ($nb--) {
            $this->channel->wait();
        }
        if ($last_message != "__QUIT__") {
            throw new Exception("Il y a trop de message");
        }
    }
}
