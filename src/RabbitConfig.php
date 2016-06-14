<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use ETNA\Silex\Provider\RabbitMQ\RabbitServiceProvider;

class RabbitConfig implements ServiceProviderInterface
{
    private $rmq_config;

    public function __construct($rmq_config = null)
    {
        $rmq_config = $rmq_config ?: [];

        $this->rmq_config['exchanges']   = isset($rmq_config['exchanges'])   ? $rmq_config['exchanges']   : [];
        $this->rmq_config['queues']      = isset($rmq_config['queues'])      ? $rmq_config['queues']      : [];
        $this->rmq_config['connections'] = isset($rmq_config['connections']) ? $rmq_config['connections'] : [];
    }

    /**
     *
     * @{inherit doc}
     */
    public function register(Container $app)
    {
        if (true !== isset($app["application_env"])) {
            throw new \Exception('$app["application_env"] is not set');
        }

        $rmq_url   = getenv('RABBITMQ_URL');
        $rmq_vhost = getenv('RABBITMQ_VHOST');

        if (false === $rmq_url) {
            throw new \Exception('RABBITMQ_URL is not defined');
        }

        if (false === $rmq_vhost) {
            throw new \Exception('RABBITMQ_VHOST is not defined');
        }

        $config        = parse_url($rmq_url);
        $rmq_producers = isset($app["rmq_producers"]) ? $app["rmq_producers"] : [];
        $rmq_consumers = isset($app["rmq_consumers"]) ? $app["rmq_consumers"] : [];

        foreach (["host", "port", "user", "pass"] as $config_key) {
            if (!isset($config[$config_key])) {
                throw new \Exception("Invalid RABBITMQ_URL : cannot resolve {$config_key}");
            }
        }

        // Set la connection
        $rabbit_config = [
            "rabbit.connections" => array_replace_recursive(
                [
                    "default"  => [
                        "host"        => $config["host"],
                        "port"        => $config["port"],
                        "user"        => $config["user"],
                        "password"    => $config["pass"],
                        "vhost"       => $rmq_vhost,
                        "ssl"         => in_array($app['application_env'], ['production', 'development']),
                        "ssl_options" => ["verify_peer" => false],
                        "options"     => [
                            'read_write_timeout' => 60,
                            'heartbeat'          => 30
                        ]
                    ]
                ],
                $this->rmq_config['connections']
            )
        ];

        // Ajoute les producers
        if (!empty($rmq_producers)) {
            $rabbit_config["rabbit.producers"] = $rmq_producers;
        }

        // Ajoute les consumers
        if (!empty($rmq_consumers)) {
            $rabbit_config["rabbit.consumers"] = $rmq_consumers;
        }

        $app["rmq.config"]    = $rabbit_config;
        $app['rmq.exchanges'] = $this->rmq_config['exchanges'];
        $app['rmq.queues']    = $this->rmq_config['queues'];

        $app->register(new RabbitServiceProvider(), $app["rmq.config"]);
    }
}
