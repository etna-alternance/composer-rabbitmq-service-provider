<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 *
 */
class RabbitMQServiceProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
        isset($app['amqp.exchanges.options']) || $app['amqp.exchanges.options'] = [];
        isset($app['amqp.queues.options'])    || $app['amqp.queues.options']    = [];
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
                            if (isset($options["ssl"]) && $options["ssl"] === true) {
                                $amqp_class = 'PhpAmqpLib\Connection\AMQPSSLConnection';
                            } else {
                                $amqp_class = 'PhpAmqpLib\Connection\AMQPConnection';
                            }

                            $amqp_args = [
                                $options["host"],
                                $options["port"],
                                $options["user"],
                                $options["password"],
                                $options["vhost"],
                                [ 'verify_peer' => false ],
                                [
                                    'read_write_timeout' => 60,
                                    'heartbeat'          => 30
                                ]
                            ];

                            $reflection = new \ReflectionClass($amqp_class);
                            $connection = $reflection->newInstanceArgs($amqp_args);

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
                        return new Exchange("", $app["amqp.chan"], []);
                    }
                );

                foreach ($app['amqp.exchanges.options'] as $name => $options) {
                    if ($name == 'default') {
                        throw new \Exception("'default' is a reserved Exchange. You can't override it.");
                    }
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
                        return new Queue("", $app["amqp.exchanges"]["default"], $app["amqp.exchanges"]["default"]->getChannel());
                    }
                );

                foreach ($app['amqp.queues.options'] as $name => $options) {
                    if ($name == "") {
                        throw new \Exception("Unamed queue is a reserved Queue. You can't override it.");
                    }
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
