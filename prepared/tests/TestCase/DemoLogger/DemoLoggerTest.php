<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 10:56 AM
 */

namespace App\DemoLogger;

use Cake\TestSuite\IntegrationTestCase;
use Mockery as m;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DemoLoggerTest extends IntegrationTestCase {

    public function setUp() {
        $this->setConnectionDependencies();
    }

    public function tearDown() {
        DemoLoggerUtil::reset();
        m::close();
    }

    private function setConnectionDependencies() {
        /** @var m\Mock $channel */
        $channel = m::mock(AMQPChannel::class);
        $channel->shouldReceive('exchange_declare')->once();
        /** @var m\Mock $connection */
        $connection = m::mock(AMQPStreamConnection::class);
        $connection->shouldReceive('channel')
            ->once()->andReturn($channel);
        $dependencies = ['connection' => $connection];
        DemoLoggerUtil::getInstance($dependencies);
    }

    /**
     * Exercising the API. setUp() has already created the instance
     */
    public function testGetInstance() {
        $instance = DemoLoggerUtil::getInstance();
        static::assertInstanceOf(DemoLoggerUtil::class, $instance);
    }

    /**
     * Exercising the API. setUp() has already created the instance
     */
    public function testGetInstanceDependencies() {
        $dependencies = ['connection' => null];
        $instance = DemoLoggerUtil::getInstance($dependencies);
        static::assertInstanceOf(DemoLoggerUtil::class, $instance);
    }

    public function testFinalizeClosesChannel() {
        $this->setFinalizeDependencies();
        DemoLoggerUtil::finalize();
    }

    private function setFinalizeDependencies() {
        DemoLoggerUtil::reset();
        /** @var m\Mock $channel */
        $channel = m::mock(AMQPChannel::class);
        $channel->shouldReceive('close')->once();
        $channel->shouldReceive('exchange_declare')->once();
        /** @var m\Mock $connection */
        $connection = m::mock(AMQPStreamConnection::class);
        $connection->shouldReceive('close')->once();
        $dependencies = ['channel' => $channel, 'connection' => $connection];
        DemoLoggerUtil::getInstance($dependencies);
    }

    public function testFinalizeClosesConnection() {
        $this->setFinalizeDependencies();
        DemoLoggerUtil::finalize();
    }

    public function testConstructorSetsMeta() {
        $actual = DemoLoggerUtil::spyProperty('meta');
        static::assertInternalType('array', $actual);
        static::assertArrayHasKey('begin', $actual);
        static::assertArrayHasKey('server', $actual);
        static::assertArrayHasKey('instanceCode', $actual);
    }

    public function testConstructorConnectsRabbitMQ() {
        // Handled in mock expectations
    }

    public function testLogPublishesMessage() {
        /** @var m\Mock $channel */
        $channel = DemoLoggerUtil::spyProperty('channel');
        $channel->shouldReceive('basic_publish')->once();
        DemoLoggerUtil::log('My Event');
    }
}
