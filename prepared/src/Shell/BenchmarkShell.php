<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/12/17
 * Time: 6:16 PM
 */

namespace App\Shell;

use App\GenerateToken\GenerateToken;
use Cake\Console\Shell;
use Cake\Database\Connection;
use Cake\Database\StatementInterface;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class BenchmarkShell extends Shell {
    private static $benchRows = 2000;
    private static $exchange = 'fest';
    private static $routingKey = 'fest_benchmark';

    /** @var array */
    private $data;
    /** @var AMQPStreamConnection */
    private $connection;
    /** @var AMQPChannel */
    private $channel;

    public function main() {
        ini_set('memory_limit', '1024M');
        $this->buildData();
        $this->connectRabbitMQ();
        $baseRate = $this->modelSingle();
        $this->saveManySingle($baseRate);
        $this->preparedSingle($baseRate);
        $this->bulkInsertSingle($baseRate);
        $this->rabbit($baseRate);
        $this->verbose('Benchmark: Done');
    }

    private function connectRabbitMQ() {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        }
        if (!$this->channel) {
            $this->channel = $this->connection->channel();
        }
        $this->channel->exchange_declare(static::$exchange, 'direct', false, false, false);
    }

    private function rabbit($baseRate) {
        $begin = microtime(true);
        foreach ($this->data as $data) {
            $message = new AMQPMessage(json_encode($data));
            $this->channel->basic_publish($message, static::$exchange, static::$routingKey);
        }
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $speedup = sprintf(', %.0f%% speedup', $per / $baseRate * 100.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' .
            sprintf('%.3f rows per second', $per) . $speedup);
    }

    private function buildData() {
        $now = date('Y-m-d H:i:s');
        $rowCount = 0;
        while ($rowCount++ < static::$benchRows) {
            $this->data[] = [
                'instance_code' => GenerateToken::token(),
                'hostname' => 'host ' . random_int(1, 10),
                'instance_begin' => $now,
                'event_time' => $now,
                'event_class' => 'class ' . random_int(0, 100),
                'event_function' => 'function ' . random_int(0, 99),
                'event' => 'Individual event ' . random_int(1, 20),
                'detail' => 'Detail ' . random_int(0, 65535),
            ];
        }
    }

    private function modelSingle() {
        $begin = microtime(true);
        $table = TableRegistry::get('FestV02Benchmarks');
        foreach ($this->data as $data) {
            $entity = $table->newEntity($data);
            $table->save($entity);
        }
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' . sprintf('%.3f rows per second', $per));
        return $per;
    }

    private function saveManySingle($baseRate) {
        $begin = microtime(true);
        $table = TableRegistry::get('FestV02Benchmarks');
        $entities = $table->newEntities($this->data);
        $table->saveMany($entities);
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $speedup = sprintf(', %.0f%% speedup', $per / $baseRate * 100.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' .
            sprintf('%.3f rows per second', $per) . $speedup);
    }

    private function bulkInsertSingle($baseRate) {
        $begin = microtime(true);
        $table = TableRegistry::get('FestV02Benchmarks');
        $query = $table->query();
        $query->insert(array_keys($this->data[0]));
        foreach ($this->data as $data) {
            $query->values($data);
        }
        $query->execute();
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $speedup = sprintf(', %.0f%% speedup', $per / $baseRate * 100.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' .
            sprintf('%.3f rows per second', $per) . $speedup);
    }

    private function preparedSingle($baseRate) {
        $begin = microtime(true);
        /** @var Connection $connection */
        $connection = ConnectionManager::get('fest');
        $sql = 'INSERT INTO fest_v02_benchmarks 
        (instance_code, hostname, instance_begin, event_time, event_class, event_function, event, detail, created) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, now())';
        /** @var StatementInterface $insert */
        $insert = $connection->prepare($sql);
        foreach ($this->data as $data) {
            $insert->execute(array_values($data));
        }
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $speedup = sprintf(', %.0f%% speedup', $per / $baseRate * 100.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' .
            sprintf('%.3f rows per second', $per) . $speedup);
    }
}
