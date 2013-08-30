<?php

namespace ETNA\Silex\Provider\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
/**
 *
 */
class Exchange
{
    private $name;
    private $channel;
    private $type        = "direct";
    private $passive     = false;
    private $durable     = false;
    private $auto_delete = true;

    public function __construct($name, AMQPChannel $channel, $options)
    {
        $this->name        = $name;
        $this->channel     = $channel;
        $this->type        = isset($options["type"])        ? $options["type"]        : "direct";
        $this->passive     = isset($options["passive"])     ? $options["passive"]     : false;
        $this->durable     = isset($options["durable"])     ? $options["durable"]     : true;
        $this->auto_delete = isset($options["auto_delete"]) ? $options["auto_delete"] : false;

        if ($name != "") {
            $channel->exchange_declare(
                $name,
                $this->type,
                $this->passive,
                $this->durable,
                $this->auto_delete
            );
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

    public function getType()
    {
        return $this->type;
    }

    public function isPassive()
    {
        return $this->passive;
    }

    public function isDurable()
    {
        return $this->durable;
    }

    public function isAutoDelete()
    {
        return $this->auto_delete;
    }

    public function send($message, $routing_key = "", $mandatory = false, $immediate = false, $ticket = null)
    {
        $message = new AMQPMessage(json_encode($message), ["Content-Type" => "application/json"]);
        $this->channel->basic_publish($message, $this->name, $routing_key, $mandatory, $immediate, $ticket);
    }
}
