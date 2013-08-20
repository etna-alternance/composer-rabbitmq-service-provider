<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use PhpAmqpLib\Connection\AMQPConnection;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * 
 */
class RabbitMQServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        $app["rabbitmq"] = $app->share(
            function ($app) {
                if (!isset($app["rabbitmq.host"])) {
                    throw new \Exception('Undefined $app["rabbitmq.host"]');
                }
                if (!isset($app["rabbitmq.port"])) {
                    throw new \Exception('Undefined $app["rabbitmq.port"]');
                }
                if (!isset($app["rabbitmq.user"])) {
                    throw new \Exception('Undefined $app["rabbitmq.user"]');
                }
                if (!isset($app["rabbitmq.password"])) {
                    throw new \Exception('Undefined $app["rabbitmq.password"]');
                }
                if (!isset($app["rabbitmq.vhost"])) {
                    throw new \Exception('Undefined $app["rabbitmq.vhost"]');
                }
                return new AMQPConnection(
                    $app["rabbitmq.host"],
                    $app["rabbitmq.port"],
                    $app["rabbitmq.user"],
                    $app["rabbitmq.password"],
                    $app["rabbitmq.vhost"]
                );
            }
        );
    }
}
