<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:48 AM
 */

namespace App\DemoLogger;

use App\GenerateToken\GenerateToken;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class DemoLoggerUtil {
    /** @var DemoLoggerUtil */
    private static $instance;

    private static $exchange = 'fest';

    private static $routingKey = 'fest_log';

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

    private $meta;

    /**
     * DemoLoggerUtil constructor: All public methods are static
     * Allows dependency injection for unit testing
     *
     * @param array $dependencies
     */
    private function __construct(array $dependencies = null) {
        if (is_array($dependencies)) {
            $this->injectDependencies($dependencies);
        }
        $this->setMeta();
        $this->connectRabbitMQ();
    }

    /**
     * Inject dependencies during unit testing
     *
     * @param array $dependencies
     */
    private function injectDependencies(array $dependencies) {
        foreach ($dependencies as $key => $value) {
            if (property_exists(static::class, $key)) {
                $this->$key = $value;
            }
        }
    }

    private function setMeta() {
        if (!(is_array($this->meta) && count($this->meta))) {
            $this->meta = [
                'begin' => sprintf('%.6f', microtime(true)),
                'server' => gethostname(),
                'instanceCode' => GenerateToken::token(),
            ];
        }
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

    public static function log($event, $detail = '') {
        $begin = sprintf('%.6f', microtime(true));
        $trace = debug_backtrace(false);
        $class = $trace[1]['class'];
        $function = $trace[1]['function'];
        unset($trace);
        $logEvent = [
            'begin' => $begin,
            'class' => $class,
            'function' => $function,
            'event' => $event,
            'detail' => $detail,
        ];
        static::getInstance()->flush($logEvent);
    }

    private function flush(array $logEvent) {
        $payload = [
            'meta' => $this->meta,
            'event' => $logEvent,
        ];
        $message = new AMQPMessage(json_encode($payload));
        $this->channel->basic_publish($message, static::$exchange, static::$routingKey);
    }

    public static function getInstance(array $dependencies = null) {
        if (!static::$instance) {
            static::$instance = new static($dependencies);
        }
        return static::$instance;
    }

    public static function finalize() {
        static::getInstance()->disconnectRabbitMQ();
    }

    private function disconnectRabbitMQ() {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * Unit test support, called during tearDown()
     */
    public static function reset() {
        static::$instance = null;
    }

    public static function spyProperty($property) {
        return static::getInstance()->$property;
    }
}
