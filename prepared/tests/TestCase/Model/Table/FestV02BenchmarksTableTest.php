<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FestV02BenchmarksTable Test Case
 */
class FestV02BenchmarksTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\FestV02BenchmarksTable
     */
    public $FestV02Benchmarks;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.fest_v02_benchmarks'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('FestV02Benchmarks') ? [] : ['className' => 'App\Model\Table\FestV02BenchmarksTable'];
        $this->FestV02Benchmarks = TableRegistry::get('FestV02Benchmarks', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->FestV02Benchmarks);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test defaultConnectionName method
     *
     * @return void
     */
    public function testDefaultConnectionName()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
