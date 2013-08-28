# language: fr

Fonctionnalité: $app["amqp.queues"]

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
                "exchange1": {
                    "channel": "default",
                    "type": "direct",
                    "passive": false,
                    "durable": false,
                    "auto_delete": true
                },
                "exchange2": {
                    "channel": "named",
                    "type": "fanout",
                    "passive": false,
                    "durable": true,
                    "auto_delete": false
                }
            },
            "amqp.queues.options": {
                "test1": {
                    "exchange": "exchange1",
                    "passive": false,
                    "durable": false,
                    "exclusive": false,
                    "auto_delete": true
                },
                "test2": {
                    "exchange": "exchange1",
                    "passive": false,
                    "durable": true,
                    "exclusive": false,
                    "auto_delete": false
                }
            }
        }
        """

    Plan du Scénario: $app["amqp.queues"]["$name"] est un object de type Queue
        Alors $app["amqp.queues"]["<name>"] est du type ETNA\Silex\Provider\RabbitMQ\Queue
        Et $app["amqp.queues"]["<name>"]->isPassive() == <passive>
        Et $app["amqp.queues"]["<name>"]->isDurable() == <durable>
        Et $app["amqp.queues"]["<name>"]->isExclusive() == <exclusive>
        Et $app["amqp.queues"]["<name>"]->isAutoDelete() == <auto_delete>
    
    Exemples:
        | name  | passive | durable | exclusive | auto_delete |
        | test1 | false   | false   | false     | true        |
        | test2 | false   | true    | false     | false       |

    Scénario: $amqp["amqp.queues"]["test2"]->send()
        Quand j'envoie un message "coucou" dans la file "test2"
        Alors il doit y avoir un message "coucou" dans la file
