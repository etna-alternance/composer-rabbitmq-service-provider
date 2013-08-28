<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * 
 */
class Queue
{
    public function __construct($name, Exchange $exchange, AMQPChannel $channel, $options)
    {
        $this->name        = $name;
        $this->exchange    = $exchange;
        $this->channel     = $channel;
        $this->passive     = $options["passive"];
        $this->durable     = $options["durable"];
        $this->exclusive   = $options["exclusive"];
        $this->auto_delete = $options["auto_delete"];

        $channel->queue_declare(
            $name,
            $this->passive,
            $this->durable,
            $this->exclusive,
            $this->auto_delete
        );
        
        if ($exchange->getName()) {
            $routing_key = $exchange->getType() == "fanout" ? null : $name;
            $channel->queue_bind($name, $exchange->getName(), $routing_key);
        }
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getChannel()
    {
        return $this->channel;
    }
    
    public function isPassive()
    {
        return $this->passive;
    }
    
    public function isDurable()
    {
        return $this->durable;
    }
    
    public function isExclusive()
    {
        return $this->exclusive;
    }
    
    public function isAutoDelete()
    {
        return $this->auto_delete;
    }

    public function send($message, $routing_key = null, $mandatory = false, $immediate = false, $ticket = null)
    {
        $routing_key = $routing_key !== null ? $routing_key : $this->name;
        $message = new AMQPMessage(json_encode($message), ["Content-Type" => "application/json"]);
        $this->channel->basic_publish($message, $this->exchange->getName(), $routing_key, $mandatory, $immediate, $ticket);
    }
}
