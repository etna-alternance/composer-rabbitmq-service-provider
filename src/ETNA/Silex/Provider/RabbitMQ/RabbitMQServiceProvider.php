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
        $app["amqp.chan"] = $app->share(
            function (Application $app) {
                return $app["amqp.chans"]["default"];
            }
        );

        $app["amqp.chans"] = $app->share(
            function (Application $app) {
                $chans = new \Pimple();

                foreach ($app['amqp.chans.options'] as $name => $options) {
                    $chans[$name] = $app->share(
                        function () use ($app, $name, $options) {
                            $connection = new AMQPConnection(
                                $options["host"],
                                $options["port"],
                                $options["user"],
                                $options["password"],
                                $options["vhost"]
                            );
                            $channel = $connection->channel();
                            register_shutdown_function(function ($channel, $connection) use ($name) {
                                $channel->close();
                                $connection->close();
                            }, $channel, $connection);
                            return $channel;
                        }
                    );
                }

                return $chans;
            }
        );

        $app["amqp.exchanges"] = $app->share(
            function (Application $app) {
                $exchanges = new \Pimple();

                $exchanges["default"] = $app->share(
                    function () use ($app) {
                        return new Exchange("", $app["amqp.chan"], [    ]);
                    }
                );

                foreach ($app['amqp.exchanges.options'] as $name => $options) {
                    $exchanges[$name] = $app->share(
                        function () use ($app, $name, $options) {
                            $channel = (isset($options["channel"])) ? $options["channel"] : "default";
                            return new Exchange($name, $app["amqp.chans"][$channel], $options);
                        }
                    );
                }

                return $exchanges;
            }
        );

        $app["amqp.queues"] = $app->share(
            function (Application $app) {
                $queues = new \Pimple();

                $queues[""] = $app->share(
                    function () use ($app) {
                        return new Exchange("", $app["amqp.chan"], [    ]);
                    }
                );

                foreach ($app['amqp.queues.options'] as $name => $options) {
                    $queues[$name] = $app->share(
                        function () use ($app, $name, $options) {
                            $exchange = (isset($options["exchange"])) ? $options["exchange"] : "default";
                            return new Queue($name, $app["amqp.exchanges"][$exchange], $app["amqp.exchanges"][$exchange]->getChannel(), $options);
                        }
                    );
                }

                return $queues;
            }
        );
    }
}
