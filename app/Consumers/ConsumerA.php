<?php

namespace TestRabbitMq\Consumers;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

use Silex\Applications;

class ConsumerA implements ConsumerInterface
{
    private $messages = [];

    public function execute(AMQPMessage $message)
    {
        $payload = json_decode($message->body, true);

        $this->messages[] = $payload;

        return true;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function flushMessages()
    {
        $this->messages = [];
    }
}
