<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 1:43 PM
 */

namespace App\DemoLogger;

use Cake\Database\Connection;
use Cake\Database\StatementInterface;

final class LookupUtil {
    private static $cacheLimit = 200;

    private static $instances = [];

    /** @var Connection */
    private $connection;

    private $table;

    private $cache = [];

    /** @var StatementInterface */
    private $query;

    /** @var StatementInterface */
    private $insert;

    private function __construct(Connection $connection, $table, array $dependencies) {
        $this->connection = $connection;
        $this->table = $table;
        if (count($dependencies)) {
            $this->injectDependencies($dependencies);
        }
        $this->prepareStatements();
    }

    private function injectDependencies(array $dependencies) {
        foreach ($dependencies as $key => $value) {
            if (property_exists(static::class, $key)) {
                $this->$key = $value;
            }
        }
    }

    private function prepareStatements() {
        if (!$this->query) {
            /** @noinspection SqlResolve */
            $sql = "select id from {$this->table} where `name` = ?";
            $this->query = $this->connection->prepare($sql);
        }
        if (!$this->insert) {
            $sql = "insert into {$this->table} (`name`, created) values (?, now())";
            $this->insert = $this->connection->prepare($sql);
        }
    }

    public static function reset() {
        static::$instances = [];
    }

    /**
     * @param Connection $connection
     * @param string $table
     * @param string $value
     * @return int
     * @throws \InvalidArgumentException
     */
    public static function lookup(Connection $connection, $table, $value) {
        $instance = static::getInstance($connection, $table);
        return array_key_exists($value, $instance->cache) ?
            $instance->cache[$value] : $instance->runLookup($value);
    }

    /**
     * @param Connection $connection
     * @param string $table
     * @param array $dependencies
     * @return LookupUtil
     */
    public static function getInstance(Connection $connection, $table, array $dependencies = []) {
        if (!array_key_exists($table, static::$instances)) {
            static::$instances[$table] = new static($connection, $table, $dependencies);
        }
        return static::$instances[$table];
    }

    /**
     * @param string $value
     * @return int
     * @throws \InvalidArgumentException
     */
    private function runLookup($value) {
        if (count($this->cache) >= static::$cacheLimit) {
            $this->cache = []; // Cache got too big; clear and start over
        }
        if (!$this->query) {
            // Should only happen when developing unit tests
            throw new \InvalidArgumentException('No query for ' . $this->table);
        }
        $parms = [substr($value, 0, 255)];
        $this->query->execute($parms);
        $row = $this->query->fetch('assoc');
        if (is_array($row) && count($row)) {
            $id = (int)$row['id'];
        } else {
            $this->insert->execute($parms);
            $id = (int)$this->insert->lastInsertId();
        }
        $this->cache[$value] = $id;
        return $id;
    }
}
