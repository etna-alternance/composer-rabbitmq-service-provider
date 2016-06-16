<?php

namespace TestRabbitMq;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

use ETNA\Silex\Provider\Config as ETNAConf;
use ETNA\Silex\Provider\RabbitMQ\RabbitConfig;
use TestRabbitMq\Consumers\ConsumerA;

class EtnaConfig implements ServiceProviderInterface
{
    private $rabbitmq_config;

    public function __construct()
    {
        $this->rabbitmq_config = [
            "exchanges" => [
                "etna" => [
                    "name"        => "etna",
                    "channel"     => "default",
                    "type"        => "direct",
                    "passive"     => false,
                    "durable"     => true,
                    "exclusive"   => false,
                    "auto_delete" => false,
                ],
            ],
            "queues" => [
                "queue_a" => [
                    "name"        => "queue_a",
                    "passive"     => false,
                    "durable"     => true,
                    "exclusive"   => false,
                    "auto_delete" => false,
                    "exchange"    => "etna",
                    "routing.key" => "queue_a",
                    "channel"     => "default",
                ],
                "queue_b" => [
                    "name"        => "queue_b",
                    "passive"     => false,
                    "durable"     => true,
                    "exclusive"   => false,
                    "auto_delete" => false,
                    "exchange"    => "etna",
                    "routing.key" => "queue_b",
                    "channel"     => "default",
                ]
            ]
        ];
    }

    /**
     *
     * @{inherit doc}
     */
    public function register(Container $app)
    {
        $app["rmq_producers"] = [
            'producer_a' => [
                'connection'       => 'default',
                'exchange_options' => $this->rabbitmq_config['exchanges']['etna'],
                'queue_options'    => ['name' => 'queue_a', 'routing_keys' => ['queue_a']]
            ]
        ];

        $app['ConsumerA'] = new ConsumerA();

        $app["rmq_consumers"] = [
            'consumer_a' => [
                'connection'        => 'default',
                'exchange_options'  => $this->rabbitmq_config['exchanges']['etna'],
                'queue_options'     => ['name' => 'queue_a', 'routing_keys' => ['queue_a']],
                'callback'          => 'ConsumerA'
            ]
        ];

        $app->register(new RabbitConfig($this->rabbitmq_config));
    }
}
