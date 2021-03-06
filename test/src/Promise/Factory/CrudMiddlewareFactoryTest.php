<?php

namespace zaboy\test\async\Promise\Factory\Broker;

use zaboy\async\Promise\CrudMiddleware;
use zaboy\async\Promise\Factory\CrudMiddlewareFactory;
use Interop\Container\ContainerInterface;
use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\rest\TableGateway\TableManagerMysql;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class CrudMiddlewareFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CrudMiddlewareFactory
     */
    protected $object;

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
        global $testCase;
        $testCase = 'table_for_test';

        $this->container = include './config/container.php';
        $this->object = new CrudMiddlewareFactory();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);
        $tableManagerMysql->deleteTable(StoreFactory::TABLE_NAME . '_test');

        global $testCase;
        $testCase = null;
    }

    public function test__invoke()
    {
        $crudMiddleware = $this->object->createService($this->container);
        $this->assertInstanceOf(
                CrudMiddleware::class, $crudMiddleware
        );
    }

}
