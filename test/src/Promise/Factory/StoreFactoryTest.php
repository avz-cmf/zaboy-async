<?php

namespace zaboy\test\async\Promise\Factory;

use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Store;
use zaboy\rest\TableGateway\TableManagerMysql;
use Interop\Container\ContainerInterface;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class StoreFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var StoreFactory
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var TableManagerMysql
     */
    protected $tableManagerMysql;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function test__invoke__DefaultTableName()
    {
        global $testCase;
        $testCase = 'default';

        $this->container = include './config/container.php';
        $this->object = new StoreFactory();
        $this->tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);

// if tables is absent or present
        $this->tableName = StoreFactory::TABLE_NAME;

        $store = $this->container->get(StoreFactory::KEY);
        $this->assertInstanceOf(
                Store::class, $store
        );
// if tables is present
        $this->assertTrue(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        $store = $this->container->get(StoreFactory::KEY);
        $this->assertInstanceOf(
                Store::class, $store
        );
    }

    public function test__invoke__TableNameAsConstructParam()
    {
        global $testCase;
        $testCase = 'default';

        $this->container = include './config/container.php';
        $this->object = new StoreFactory();
        $this->tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);

        // if tables is absent
        $this->tableName = 'promises_test_construct_param';
        $this->tableManagerMysql->deleteTable($this->tableName);

        $this->assertFalse(
                $this->tableManagerMysql->hasTable($this->tableName)
        );

        $store = $this->object->__invoke($this->container, '', [StoreFactory::KEY_TABLE_NAME => $this->tableName]);
        $this->assertInstanceOf(
                Store::class, $store
        );
        // if tables is present
        $this->assertTrue(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        $store = $this->object->__invoke($this->container, '', [StoreFactory::KEY_TABLE_NAME => $this->tableName]);
        $this->assertInstanceOf(
                Store::class, $store
        );
        $this->tableManagerMysql->deleteTable($this->tableName);
    }

    public function testStoreFactory__invoke__TableNameFromConfig()
    {
        global $testCase;
        $testCase = 'table_for_test';

        $this->container = include './config/container.php';
        $this->object = new StoreFactory();
        $this->tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);

// if tables is absent
        $this->tableName = StoreFactory::TABLE_NAME . '_test';
        $this->tableManagerMysql->deleteTable($this->tableName);
        $this->assertFalse(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        /* @var $store Store */
        $store = $this->container->get(StoreFactory::KEY);
        $this->assertInstanceOf(
                Store::class, $store
        );
// if tables is present
        $this->assertTrue(
                $this->tableManagerMysql->hasTable($this->tableName)
        );
        $store = $this->container->get(StoreFactory::KEY);
        $this->assertInstanceOf(
                Store::class, $store
        );
        $this->tableManagerMysql->deleteTable($this->tableName);
        $testCase = 'default';
    }

}