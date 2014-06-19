# language: fr

Fonctionnalité: $app["amqp.queues"][""]
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
                }
            },
            "amqp.queues.options": {
                "": {
                    "exchange": "exchange1",
                    "passive": false,
                    "durable": false,
                    "exclusive": false,
                    "auto_delete": true
                }
            }
        }
        """

    Scénario: Queue réservée
        Etant donné que "" est une queue réservé
        Alors je devrais avoir une exception "Unamed queue is a reserved Queue. You can't override it."
