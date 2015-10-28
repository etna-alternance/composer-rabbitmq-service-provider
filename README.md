rabbitmq-service-provider
=========================

[![Build Status](http://drone.etna-alternance.net/github.com/etna-alternance/composer-rabbitmq-service-provider/status.svg?branch=master)](http://drone.etna-alternance.net/github.com/etna-alternance/composer-rabbitmq-service-provider)
[![Dependency Status](https://www.versioneye.com/user/projects/53dde6d68e78abc191000017/badge.svg)](https://www.versioneye.com/user/projects/53dde6d68e78abc191000017)
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
La version 2.6.0 de videlalvaro/php-amqplib génère des erreurs et bloque les jobs trop gros par conséquent on reste sur la version 2.5.2
