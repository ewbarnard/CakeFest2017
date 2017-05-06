<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:56 AM
 */

namespace App\DemoLogger;

use Cake\TestSuite\IntegrationTestCase;

class DemoLoggerTest extends IntegrationTestCase {

    public function testGetInstance() {
        $instance = DemoLoggerUtil::getInstance();
        static::assertInstanceOf(DemoLoggerUtil::class, $instance);
    }
}
