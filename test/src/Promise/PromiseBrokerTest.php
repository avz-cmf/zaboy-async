<?php

namespace zaboy\test\async\PromiseClient;

use zaboy\async\Promise\Factory\Adapter\MySqlAdapterFactory;
use zaboy\async\Promise\Factory\PromiseBrokerFactory;
use zaboy\async\Promise\PromiseBroker;
use Interop\Container\ContainerInterface;
use zaboy\rest\TableGateway\TableManagerMysql;

class PromiseBrokerTest extends \PHPUnit_Framework_TestCase
{

    const TEST_TABLE_NAME = 'test_mysqlpromisebroker';

    /**
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var PromiseBroker
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
        $this->container = include './config/container.php';
        $this->adapter = $this->container->get('db');
        $promiseBrokerFactory = new PromiseBrokerFactory();
        $this->object = $promiseBrokerFactory->__invoke(
                $this->container
                , ''
                , [MySqlAdapterFactory::KEY_PROMISE_TABLE_NAME => self::TEST_TABLE_NAME]
        );
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

    public function testPromiseBrokerTest__makePromise()
    {
        $promiseId = $this->object->makePromise();

        $promise = $this->object->getPromise($promiseId);
        $this->assertSame(
                get_class($promise), 'zaboy\async\Promise\PromiseClient'
        );

        $this->assertEquals(
                $promise->cancelFn, null
        );
        $this->assertEquals(
                $promise->waitFn, null
        );
        $this->assertEquals(
                $promise->waitList, []
        );

        $this->assertEquals(
                $promise->state, 'pending'
        );
        $this->assertEquals(
                $promise->result, null
        );
        $this->assertEquals(
                strpos($promise->promiseId, 'promise'), 0
        );
        $time = $promise->timeEnd - PromiseBroker::DEFAULT_MAX_LIFE_TIME;
        $this->assertTrue(
                abs($time - (microtime(1) - date('Z'))) < 10
        );
    }

    public function testPromiseBrokerTest__getPromise()
    {
        $promiseId = $this->object->makePromise();
        $this->assertSame(
                get_class($this->object->getPromise($promiseId)), 'zaboy\async\Promise\PromiseClient'
        );
        $this->assertEquals(
                get_class($this->object->getPromise($promiseId)), 'zaboy\async\Promise\PromiseClient'
        );
    }

}
