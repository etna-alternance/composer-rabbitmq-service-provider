<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * 
 */
class Queue
{
    function __construct($name, Exchange $exchange, AMQPChannel $channel, $options)
    {
        $this->name    = $name;
        $this->channel = $channel;
        $this->options = $options;
        $channel->exchange_declare(
            $name,
            $options["type"],
            $options["passive"],
            $options["durable"],
            $options["auto_delete"]
        );
    }

    public function send($message, $routing_key = "", $mandatory = false, $immediate = false, $ticket = null)
    {
        $message = new AMQPMessage(json_encode($message), ["Content-Type" => "application/json"]);
        $this->channel->basic_publish($message, $this->name, $routing_key, $mandatory, $immediate, $ticket);
    }
}
