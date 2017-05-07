<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 12:30 PM
 */

namespace App\Shell;

use Cake\Console\Shell;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumerTodoShell extends Shell {
    private static $exchange = 'fest';

    private static $routingKey = 'fest_log';

    private $queueName;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    public function main() {
        $this->myInitialize();
        $this->consume();
    }

    private function myInitialize() {
        $this->connectRabbitMQ();
        $this->prepareStatements();
    }

    private function connectRabbitMQ() {
        $this->connection = new AMQPStreamConnection('localhost', 5672,
            'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare(static::$exchange, 'direct', false,
            false, false);
        $this->queueName = static::$exchange . '_' . static::$routingKey;
        $this->channel->queue_declare($this->queueName, false, true,
            false, false);
        $this->channel->queue_bind($this->queueName, static::$exchange, static::$routingKey);
    }

    private function prepareStatements() {
        // TODO #1
    }

    private function consume() {
        $callback = function ($msg) {
            $this->processMessage($msg->body);
        };
        $this->channel->basic_consume($this->queueName, '', false, true, false, false, $callback);
        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
        $this->channel->close();
        $this->connection->close();
    }

    public function processMessage($payload) {
        // TODO #2

        $message = json_decode($payload, true);
        $this->verbose($payload);
        pr($message);
    }

}
