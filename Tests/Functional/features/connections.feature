# language: fr

Fonctionnalité: Tester les connections

Scénario: Vérifier les connections
    Alors la connection "default" devrait être définie
    Et    la connection "default" devrait être une instance de "PhpAmqpLib\Connection\AMQPConnection"

