<?php

namespace TestContext;

use Behat\Behat\Context\SnippetAcceptingContext;
use PhpAmqpLib\Message\AMQPMessage;
use ETNA\FeatureContext\BaseContext;

/**
 * Features context
 */
class FeatureContext extends BaseContext implements SnippetAcceptingContext
{
    /**
     * @BeforeScenario
     */
    public static function createExchangeAndQueue()
    {
        $channel = self::$silex_app['rabbit.producer']['producer_a']->getChannel();
        $channel->exchange_declare('etna', 'direct', false, true, false);

        $queue_opt = self::$silex_app['rmq.queues']["queue_a"];
        $channel->queue_declare(
            $queue_opt["name"],
            $queue_opt["passive"],
            $queue_opt["durable"],
            $queue_opt["exclusive"],
            $queue_opt["auto_delete"]
        );

        $channel->queue_bind($queue_opt['name'], $queue_opt['exchange'], $queue_opt['routing.key']);
    }

    /**
     * @AfterScenario @consumer
     */
    public static function flushMessages()
    {
        var_dump("LOLILOUL");
        self::$silex_app['ConsumerA']->flushMessages();
        self::$silex_app['rabbit.consumer']['consumer_a']->purge();
        self::$silex_app['rabbit.consumer']['consumer_a']->resetConsumed();
    }

    /**
     * @Then la connection :connection devrait être définie
     */
    public function laConnectionDevraitEtreDefinie($connection)
    {
        if (false === isset(self::$silex_app['rabbit.connection'][$connection])) {
            throw new \Exception("Connection {$connection} is not defined");
        }

        return self::$silex_app['rabbit.connection'][$connection];
    }

    /**
     * @Then la connection :conn devrait être une instance de :class_name
     */
    public function laConnectionDevraitEtreUneInstanceDe($conn, $class_name)
    {
        $connection = $this->laConnectionDevraitEtreDefinie($conn);
        if (false === $connection instanceof $class_name) {
            $actual_class = get_class($connection);
            throw new \Exception("Expected connection {$conn} to be instance of {$class_name}. Instance of {$actual_class} instead.");
        }
    }

    /**
     * @Then le producer :producer devrait être défini
     */
    public function leProducerDevraitEtreDefini($producer)
    {
        if (false === isset(self::$silex_app['rabbit.producer'][$producer])) {
            throw new \Exception("Producer {$producer} is not defined");
        }

        return self::$silex_app['rabbit.producer'][$producer];
    }

    /**
     * @Then le producer :producer devrait être une instance de :class_name
     */
    public function leProducerDevraitEtreUneInstanceDe($producer_name, $class_name)
    {
        $producer = $this->leProducerDevraitEtreDefini($producer_name);
        if (false === $producer instanceof $class_name) {
            $actual_class = get_class($producer);
            throw new \Exception("Expected producer {$producer_name} to be instance of {$class_name}. Instance of {$actual_class} instead.");
        }
    }

    /**
     * @When je publie un job via le producer :producer_name avec le corps contenu dans :body_file
     */
    public function jePublieUnJobViaLeProducerAvecLeCorpsContenuDans($producer_name, $body_file)
    {
        $body = file_get_contents($this->requests_path . $body_file);
        if (!$body) {
            throw new \Exception("File not found : {$this->requests_path}${body_file}");
        }

        $producer    = $this->leProducerDevraitEtreDefini($producer_name);
        $routing_key = self::$silex_app['rabbit.producers'][$producer_name]['queue_options']['routing_keys'][0];
        $producer->publish($body, $routing_key);
    }

    /**
     * @Then le consumer :consumer devrait être défini
     */
    public function leConsumerDevraitEtreDefini($consumer)
    {
        if (false === isset(self::$silex_app['rabbit.consumer'][$consumer])) {
            throw new \Exception("Consumer {$consumer} is not defined");
        }

        return self::$silex_app['rabbit.consumer'][$consumer];
    }

    /**
     * @Then le consumer :consumer_name devrait être une instance de :class_name
     */
    public function leConsumerDevraitEtreUneInstanceDe($consumer_name, $class_name)
    {
        $consumer = $this->leConsumerDevraitEtreDefini($consumer_name);
        if (false === $consumer instanceof $class_name) {
            $actual_class = get_class($consumer);
            throw new \Exception("Expected consumer {$consumer_name} to be instance of {$class_name}. Instance of {$actual_class} instead.");
        }
    }

    /**
     * @Given que je publie un message pour le consumer :consumer_name avec le corps contenu dans :body_file
     */
    public function queJePublieUnMessagePourLeConsumerAvecLeCorpsContenuDans($consumer_name, $body_file)
    {
        $body = file_get_contents($this->requests_path . $body_file);
        if (!$body) {
            throw new \Exception("File not found : {$this->requests_path}${body_file}");
        }

        $consumer    = $this->leConsumerDevraitEtreDefini($consumer_name);
        $channel     = $consumer->getChannel();
        $exchange    = self::$silex_app['rabbit.consumers'][$consumer_name]['exchange_options']['name'];
        $routing_key = self::$silex_app['rabbit.consumers'][$consumer_name]['queue_options']['routing_keys'][0];
        $message     = new AMQPMessage($body, ["Content-Type" => "application/json"]);

        $channel->basic_publish($message, $exchange, $routing_key);
    }

    /**
     * @When le consumer :consumer_name consomme :nb_messages message
     */
    public function leConsumerConsommeMessage($consumer_name, $nb_messages)
    {
        $consumer = $this->leConsumerDevraitEtreDefini($consumer_name);

        $consumer->consume($nb_messages);
    }

    /**
     * @Then le consumer :consumer_name devrait avoir consommé :nb_messages message avec le corps contenu dans :result_file
     */
    public function leConsumerDevraitAvoirConsommeMessageAvecLeCorpsContenuDans($consumer_name, $nb_messages, $result_file)
    {
        $body = file_get_contents($this->results_path . $result_file);
        if (!$body) {
            throw new \Exception("File not found : {$this->results_path}${result_file}");
        }
        $callback_name     = self::$silex_app['rabbit.consumers'][$consumer_name]['callback'];
        $consumed_messages = self::$silex_app[$callback_name]->getMessages();

        $actual_count = count($consumed_messages);
        if (intval($nb_messages) !== $actual_count) {
            throw new \Exception("Expected {$nb_messages} consumed messages, got {$actual_count}");
        }

        if (json_decode($body, true) !== $consumed_messages) {
            $actual_body = json_encode($consumed_messages);

            throw new \Exception("Expected consumed messages to be {$body}, got {$actual_body}");
        }
    }
}
