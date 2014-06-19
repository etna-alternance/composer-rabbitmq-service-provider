# language: fr

Fonctionnalité: $app["amqp.exchanges"]["default"]
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
                "default": {
                    "channel": "default",
                    "type": "direct",
                    "passive": false,
                    "durable": false,
                    "auto_delete": true
                }
            }
        }
        """

    Scénario: Exchange réservé
        Etant donné que "default" est un exchange réservé
        Alors je devrais avoir une exception "'default' is a reserved Exchange. You can't override it."
