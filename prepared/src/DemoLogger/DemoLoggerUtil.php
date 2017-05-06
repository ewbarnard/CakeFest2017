<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:48 AM
 */

namespace App\DemoLogger;

final class DemoLoggerUtil {
    /** @var DemoLoggerUtil */
    private static $instance;

    /**
     * DemoLoggerUtil constructor: All public methods are static
     */
    private function __construct() {
    }

    public static function getInstance() {
        if (!static::$instance) {
            static::$instance = new static;
        }
        return static::$instance;
    }
}
