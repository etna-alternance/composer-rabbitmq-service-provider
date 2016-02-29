rabbitmq-service-provider
=========================

[![Build Status](http://drone.etna-alternance.net/api/badge/github.com/etna-alternance/composer-rabbitmq-service-provider/status.svg?branch=master)](http://drone.etna-alternance.net/github.com/etna-alternance/composer-rabbitmq-service-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/etna-alternance/composer-rabbitmq-service-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/etna-alternance/composer-rabbitmq-service-provider/?branch=master)

Installation
------------

Modifier `composer.json` :

```
{
    // ...
    "require": {
        "etna/rabbitmq-service-provider": "~0.1"
    },
    "repositories": [
       {
           "type": "composer",
           "url": "http://blu-composer.herokuapp.com"
       }
   ]
}
```

##Configuration :

Dans la conf (EtnaConfig.php)

```
$this->rabbitmq_config = [
    'exchanges' => [
        'my_exchange' => [
            'name'        => 'my_exchange',
            'channel'     => 'default',
            'type'        => 'direct',
            'passive'     => false,
            'durable'     => true,
            'auto_delete' => false,
        ],
    ],
    'queues' => [
        'my_queue' => [
            'name'        => 'email',
            'passive'     => false,
            'durable'     => true,
            'exclusive'   => false,
            'auto_delete' => false,
            'exchange'    => 'default',
            'routing.key' => 'my_routing_key',
            'channel'     => 'default',
        ]
    ],
];

$app["rmq_producers"] = [
    'my_producer' => [
        'connection'        => 'default',
        'exchange_options'  => ['name' => 'default', 'type' => 'direct']
    ]
];

$app["rmq_consumers"] = [
    'my_consumer' => [
        'connection'        => 'default',
        'exchange_options'  => ['name' => 'default','type' => 'direct'],
        'queue_options'     => $this->rabbitmq_config['queues']['my_queue'],
        'callback'          => 'my_consumer'
    ]
];

$app->register(new ETNAConf\RabbitMQ($this->rabbitmq_config));
```

###Attention :

Les callbacks utilisées pour les consumers doivent être des services registered au sein de l'app

ex : `$app['my_consumer'] = ApplicationName\Consumer\MyConsumer::handleJobs`
