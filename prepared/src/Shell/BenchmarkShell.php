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

class BenchmarkShell extends Shell {
    private static $benchRows = 1000;

    /** @var array */
    private $data;

    public function main() {
        $this->buildData();
        $this->modelSingle();
        $this->preparedSingle();
        $this->verbose('Benchmark: Done');
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
        $table = TableRegistry::get('FestV02Benchmarks');
        $begin = microtime(true);
        foreach ($this->data as $data) {
            $entity = $table->newEntity($data);
            $table->save($entity);
        }
        $interval = (microtime(true) - $begin);
        $per = (float)static::$benchRows / $interval;
        $elapsed = sprintf('%.2f', $interval * 1000.0);
        $this->verbose(__FUNCTION__ . ': ' . $elapsed . ' ms, ' . sprintf('%.3f rows per second', $per));
    }

    private function preparedSingle() {
        /** @var Connection $connection */
        $connection = ConnectionManager::get('fest');
        $sql = 'INSERT INTO fest_v02_benchmarks 
        (instance_code, hostname, instance_begin, event_time, event_class, event_function, event, detail, created) 
        VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, now())';
        /** @var StatementInterface $insert */
        $insert = $connection->prepare($sql);
        $insert->execute([]);
    }
}
