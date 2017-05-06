<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 12:30 PM
 */

namespace App\Shell;

use App\DemoLogger\LookupUtil;
use Cake\Console\Shell;
use Cake\Database\Connection;
use Cake\Database\StatementInterface;
use Cake\Datasource\ConnectionManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConsumerShell extends Shell {
    private static $cacheLimit = 200;

    private static $exchange = 'fest';

    private static $routingKey = 'fest_log';

    private $queueName;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    /** @var StatementInterface */
    private $queryInstance;

    /** @var StatementInterface */
    private $insertInstance;

    /** @var StatementInterface */
    private $insertLog;

    /** @var Connection */
    private $db;

    private $message;

    private $instanceCache = [];

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
        $this->db = ConnectionManager::get('fest');

        $sql = 'SELECT id FROM fest_instances WHERE archive_month = ? AND instance_code = ? LIMIT 1';
        $this->queryInstance = $this->db->prepare($sql);

        $sql = 'INSERT INTO fest_instances 
        (archive_month, fest_server_id, instance_code, instance_begin, created) 
        VALUES 
        (month(curdate()), ?, ?, ?, now())';
        $this->insertInstance = $this->db->prepare($sql);

        $sql = 'INSERT INTO fest_logs 
        (archive_month, fest_instance_id, fest_class_id, fest_function_id, fest_event_id, event_time, detail, created) 
        VALUES 
        (month(curdate()), ?, ?, ?, ?, ?, ?, now())';
        $this->insertLog = $this->db->prepare($sql);
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
        $this->message = json_decode($payload, true);
        $this->verbose($payload);
        if (is_array($this->message) && count($this->message)) {
            $detail = $this->message['event']['detail'];
            $detail = substr($detail, 0, 255);
            $parms = [
                $this->lookupInstance(),
                $this->lookup('fest_classes', 'class'),
                $this->lookup('fest_functions', 'function'),
                $this->lookup('fest_events', 'event'),
                $this->timestamp('event'),
                $detail,
            ];
            $this->insertLog->execute($parms);
        }
    }

    private function lookupInstance() {
        $code = $this->message['meta']['instanceCode'];
        if (array_key_exists($code, $this->instanceCache)) {
            return $this->instanceCache[$code];
        }
        if (count($this->instanceCache) >= static::$cacheLimit) {
            $this->instanceCache = [];
        }
        $parms = [date('n'), $code];
        $this->queryInstance->execute($parms);
        $row = $this->queryInstance->fetch('assoc');
        if (is_array($row) && count($row)) {
            $id = (int)$row['id'];
        } else {
            $server = $this->message['meta']['server'];
            $serverId = LookupUtil::lookup($this->db, 'fest_servers', $server);
            $begin = $this->timestamp('meta');
            $parms = [$serverId, $code, $begin];
            $this->insertInstance->execute($parms);
            $id = (int)$this->insertInstance->lastInsertId();
        }
        $this->instanceCache[$code] = $id;
        return $id;
    }

    private function timestamp($section) {
        $microtime = $this->message[$section]['begin'];
        return date('Y-m-d H:i:s', (int)$microtime);
    }

    private function lookup($table, $field) {
        $value = $this->message['event'][$field];
        return LookupUtil::lookup($this->db, $table, $value);
    }
}
