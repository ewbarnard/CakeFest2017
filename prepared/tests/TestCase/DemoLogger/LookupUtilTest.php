<?php
/**
 * Created by PhpStorm.
 * User: ewb
 * Date: 5/6/17
 * Time: 1:48 PM
 */

namespace App\DemoLogger;

use Cake\Database\Connection;
use Cake\TestSuite\IntegrationTestCase;
use Mockery as m;

class LookupUtilTest extends IntegrationTestCase {

    private static $table = 'fest_classes';
    /** @var Connection */
    private $connection;

    public function setUp() {
        $this->connection = m::mock(Connection::class);
        /** @var m\Mock $mock */
        $mock = $this->connection;
        $mock->shouldReceive('prepare');
        LookupUtil::getInstance($this->connection, static::$table);
    }

    public function tearDown() {
        LookupUtil::reset();
        m::close();
    }

    public function testGetInstance() {
        $instance = LookupUtil::getInstance($this->connection, static::$table);
        static::assertInstanceOf(LookupUtil::class, $instance);
    }

    public function testAsFixture() {
        $table = 'fest_events';
        $cache = ['cache' => ['Event Two' => 2, 'Event Three' => 3]];
        LookupUtil::getInstance($this->connection, $table, $cache);
        static::assertSame(2, LookupUtil::lookup($this->connection, $table, 'Event Two'));
        static::assertSame(3, LookupUtil::lookup($this->connection, $table, 'Event Three'));
    }
}
