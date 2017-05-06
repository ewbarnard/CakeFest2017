<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 12:30 PM
 */

namespace App\Shell;

use Cake\Console\Shell;
use Cake\Database\Connection;
use Cake\Database\StatementInterface;
use Cake\Datasource\ConnectionManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumerShell extends Shell {
    private static $exchange = 'fest';

    private static $routingKey = 'fest_log';

    private $queueName;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    /** @var StatementInterface */
    private $insertDemoLog;

    public function main() {
        $this->myInitialize();
        $this->consume();
    }

    private function myInitialize() {
        $this->connectRabbitMQ();
        $this->prepareStatements();
    }

    private function connectRabbitMQ() {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->exchange_declare(static::$exchange, 'direct', false, false, false);
        $this->queueName = static::$exchange . '_' . static::$routingKey;
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        $this->channel->queue_bind($this->queueName, static::$exchange, static::$routingKey);
    }

    private function prepareStatements() {
        /** @var Connection $connection */
        $connection = ConnectionManager::get('fest');

        $sql = 'INSERT INTO fest_log_v01 
        (archive_month, instance_code, hostname, instance_begin, event_time, event_class, event_function, event, 
        detail, created) 
        VALUES 
        (month(curdate()), ?, ?, ?, ?, ?, ?, ?, ?, now())';
        $this->insertDemoLog = $connection->prepare($sql);
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
        $message = json_decode($payload, true);
        $this->verbose($payload);
        if (is_array($message) && count($message)) {
            $parms = [
                $message['meta']['instanceCode'],
                $this->varchar($message['meta']['server']),
                $this->timestamp($message['meta']['begin']),
                $this->timestamp($message['event']['begin']),
                $this->varchar($message['event']['class']),
                $this->varchar($message['event']['function']),
                $this->varchar($message['event']['event']),
                $this->varchar($message['event']['detail']),
            ];
            $this->insertDemoLog->execute($parms);
        }
    }

    private function varchar($string, $length = 255) {
        return substr($string, 0, $length);
    }

    private function timestamp($microtime) {
        return date('Y-m-d H:i:s', (int)$microtime);
    }
}
