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

    Plan du Scénario: $app["amqp.exchanges"]["$name"] est un object de type Exchange
        Alors $app["amqp.exchanges"]["<name>"] est du type ETNA\Silex\Provider\RabbitMQ\Exchange
        Et $app["amqp.exchanges"]["<name>"]->getType() == "<type>"
        Et $app["amqp.exchanges"]["<name>"]->isPassive() == <passive>
        Et $app["amqp.exchanges"]["<name>"]->isDurable() == <durable>
        Et $app["amqp.exchanges"]["<name>"]->isAutoDelete() == <auto_delete>

    Exemples:
        | name    | type   | passive | durable | auto_delete |
        | default | direct | false   | true    | false       |
        | test1   | direct | false   | false   | true        |
        | test2   | fanout | false   | true    | false       |

    Scénario: $amqp["amqp.exchanges"]["test1"]->send()
        Etant donnée que je bind une file sur l'exchange "test1"
        Quand j'envoie un message "coucou" dans l'exchange "test1"
        Alors il doit y avoir un message "coucou" dans la file
