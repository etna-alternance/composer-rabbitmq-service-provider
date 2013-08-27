# language: fr

Fonctionnalité: $app["amqp.chans"]

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
            }
        }
        """

    Scénario: $app["amqp.chan"] est un sucre syntaxique
        Alors $app["amqp.chan"] == $app["amqp.chans"]["default"]

    Scénario: $app["amqp.chans"]["named"] est directement l'objet Channel de la lib qu'on utilise
        Alors $app["amqp.chans"]["named"] est du type PhpAmqpLib\Channel\AMQPChannel

    Scénario: $app["amqp.chans"]["default"] est directement l'objet Channel de la lib qu'on utilise
        Alors $app["amqp.chans"]["default"] est du type PhpAmqpLib\Channel\AMQPChannel
