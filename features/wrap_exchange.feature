# language: fr

Fonctionnalité: $app["amqp.exchanges"]

    Contexte:
        Soit une application Silex
        Et la configuration suivante :
        """
        {
            "amqp.chans.options": {
                "default": {
                    "host": "localhost",
                    "port": 5672,
                    "user": "guest",
                    "password": "guest",
                    "vhost": "/test-behat"
                },
                "named": {
                    "host": "localhost",
                    "port": 5672,
                    "user": "guest",
                    "password": "guest",
                    "vhost": "/test-behat-named"
                }
            },
            "amqp.exchanges.options": {
                "test1": {
                    "channel": "default",
                    "type": "direct",
                    "passive": false,
                    "durable": false,
                    "auto_delete": true
                },
                "test2": {
                    "channel": "named",
                    "type": "fanout",
                    "passive": false,
                    "durable": true,
                    "auto_delete": false
                }
            }
        }
        """

    Scénario: $app["amqp.exchanges"]["test1"] est un object de type Exchange
        Alors $app["amqp.exchanges"]["test1"] est du type ETNA\Silex\Provider\RabbitMQ\Exchange
        Et $app["amqp.exchanges"]["test1"]->getType() == "direct"
        Et $app["amqp.exchanges"]["test1"]->isPassive() == false
        Et $app["amqp.exchanges"]["test1"]->isDurable() == false
        Et $app["amqp.exchanges"]["test1"]->isAutoDelete() == true
        

    Scénario: $app["amqp.exchanges"]["test2"] est un object de type Exchange
        Alors $app["amqp.exchanges"]["test2"] est du type ETNA\Silex\Provider\RabbitMQ\Exchange
        Et $app["amqp.exchanges"]["test2"]->getType() == "fanout"
        Et $app["amqp.exchanges"]["test2"]->isPassive() == false
        Et $app["amqp.exchanges"]["test2"]->isDurable() == true
        Et $app["amqp.exchanges"]["test2"]->isAutoDelete() == false
