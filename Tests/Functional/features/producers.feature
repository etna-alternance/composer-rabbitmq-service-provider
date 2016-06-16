# language: fr

Fonctionnalité: Tester les producers

Scénario: Vérifier les producers
    Alors le producer "producer_a" devrait être défini
    Et    le producer "producer_a" devrait être une instance de "OldSound\RabbitMqBundle\RabbitMq\Producer"

Scénario: Publier un job
    Quand je publie un job via le producer "producer_a" avec le corps contenu dans "job.json"
    Alors le producer "producer_a" devrait avoir publié un message dans la queue "queue_a" avec le corps contenu dans "job_result.json"

