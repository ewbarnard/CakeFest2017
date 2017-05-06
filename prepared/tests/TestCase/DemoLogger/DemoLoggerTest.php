<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:56 AM
 */

namespace App\DemoLogger;

use Cake\TestSuite\IntegrationTestCase;
use \Mockery as m;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DemoLoggerTest extends IntegrationTestCase {

    public function tearDown() {
        DemoLoggerUtil::reset();
        m::close();
    }

    public function testGetInstance() {
        $instance = DemoLoggerUtil::getInstance();
        static::assertInstanceOf(DemoLoggerUtil::class, $instance);
    }

    public function testGetInstanceDependencies() {
        $dependencies = ['connection' => null];
        $instance = DemoLoggerUtil::getInstance($dependencies);
        static::assertInstanceOf(DemoLoggerUtil::class, $instance);
    }

    public function testFinalizeClosesChannel() {
        $this->setFinalizeDependencies();
        DemoLoggerUtil::finalize();
    }

    public function testFinalizeClosesConnection() {
        $this->setFinalizeDependencies();
        DemoLoggerUtil::finalize();
    }

    private function setFinalizeDependencies() {
        /** @var m\Mock $channel */
        $channel = m::mock(AMQPChannel::class);
        $channel->shouldReceive('close')->once();
        /** @var m\Mock $connection */
        $connection = m::mock(AMQPStreamConnection::class);
        $connection->shouldReceive('close')->once();
        $dependencies = ['channel' => $channel, 'connection' => $connection];
        DemoLoggerUtil::getInstance($dependencies);
    }
}
