# language: fr

@consumer
Fonctionnalité: Tester les consumers

Scénario: Vérifier les consumers
    Alors le consumer "consumer_a" devrait être défini
    Et    le consumer "consumer_a" devrait être une instance de "OldSound\RabbitMqBundle\RabbitMq\Consumer"

Scénario: Consommer un job
    Etant donné que je publie un message pour le consumer "consumer_a" avec le corps contenu dans "job.json"
    Quand           le consumer "consumer_a" consomme "1" message
    Alors           le consumer "consumer_a" devrait avoir consommé 1 message avec le corps contenu dans "1_job_consumed.json"

Scénario: Consommer plusieurs jobs
    Etant donné que je publie un message pour le consumer "consumer_a" avec le corps contenu dans "job1.json"
    Et          que je publie un message pour le consumer "consumer_a" avec le corps contenu dans "job2.json"
    Et          que je publie un message pour le consumer "consumer_a" avec le corps contenu dans "job3.json"
    Quand       le consumer "consumer_a" consomme 3 message
    Alors       le consumer "consumer_a" devrait avoir consommé 3 message avec le corps contenu dans "3_jobs_consumed.json"

