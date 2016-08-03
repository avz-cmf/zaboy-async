<?php

namespace zaboy\test\async\Promise\Factory\Adapter;

use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\rest\TableGateway\TableManagerMysql;
use Interop\Container\ContainerInterface;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class MySqlAdapterFactoryTest extends \PHPUnit_Framework_TestCase
{

    const TEST_TABLE_NAME = 'test_mysqlpromiseadapterfactory';

    /**
     * @var MySqlAdapterFactory
     */
    protected $object;

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->adapter = $this->container->get('db');
        $this->object = new MySqlAdapterFactory();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $tableManagerMysql = new TableManagerMysql($this->adapter);
        $tableManagerMysql->deleteTable(self::TEST_TABLE_NAME);
    }

    public function testMySqlAdapterFactory__invoke()
    {
        // if tables is absent
        $adapter = $this->object->__invoke($this->container, '', [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => self::TEST_TABLE_NAME]);
        $this->assertSame(
                get_class($adapter), 'zaboy\async\Promise\Adapter\MySqlPromiseAdapter'
        );
        // if tables is present
        $adapter = $this->object->__invoke($this->container, '', [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => self::TEST_TABLE_NAME]);
        $this->assertSame(
                get_class($adapter), 'zaboy\async\Promise\Adapter\MySqlPromiseAdapter'
        );
    }

}