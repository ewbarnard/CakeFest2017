<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:48 AM
 */

namespace App\DemoLogger;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class DemoLoggerUtil {
    /** @var DemoLoggerUtil */
    private static $instance;

    /** @var AMQPStreamConnection */
    private $connection;

    /** @var AMQPChannel */
    private $channel;

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

    public static function getInstance(array $dependencies = null) {
        if (!static::$instance) {
            static::$instance = new static($dependencies);
        }
        return static::$instance;
    }

    public static function log($event, $detail = '') {
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
}
