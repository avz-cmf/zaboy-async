<?php

namespace zaboy\test\async\Promise;

use zaboy\async\Promise\Factory\StoreFactory;
use zaboy\async\Promise\Store;
use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\HttpClient;
use zaboy\async\Promise\Client;
use zaboy\rest\TableGateway\TableManagerMysql;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class CrudMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HttpClient
     */
    protected $object;

    /**
     * @var Store
     */
    protected $store;

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
        $this->object = $this->container->get('test_crud_client');
        $this->store = $this->container->get(StoreFactory::KEY);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        /* @var $store Store */
        $store = $this->container->get(StoreFactory::KEY);
        $tableName = $store->table;
        /* @var $tableManagerMysql TableManagerMysql */
        $tableManagerMysql = $this->container->get(TableManagerMysql::KEY_IN_CONFIG);
        $tableManagerMysql->deleteTable($tableName);
    }

    public function test__createPromise()
    {

        $promiseData = $this->object->create([]);
        $promise = new Client($this->store, $promiseData[Store::ID]);
        $this->assertInstanceOf(
                Client::class, $promise
        );
        $this->assertEquals(
                Client::PENDING, $promise->getState()
        );
    }

    public function test_resolvePromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Client::FULFILLED;
        $promiseData[Store::RESULT] = 'test_result_success_fulfill';

        $this->object->update($promiseData);

        $promise = new Client($this->store, $promiseData[Store::ID]);
        $result = $promise->wait(false);
        $this->assertEquals(
                'test_result_success_fulfill', $result
        );
        $this->assertEquals(
                Client::FULFILLED, $promise->getState()
        );
    }

    public function test_rejectPromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Client::REJECTED;
        $promiseData[Store::RESULT] = 'test_result_error_reject';

        $this->object->update($promiseData);

        $promise = new Client($this->store, $promiseData[Store::ID]);
        $result = $promise->wait(false);
        $this->assertInstanceOf(
                'zaboy\async\Promise\Exception\RejectedException', $result
        );
        $this->assertEquals(
                Client::REJECTED, $promise->getState()
        );
    }

    public function test_tryToChangeFulfilledPromise()
    {
        $promiseData = $this->object->create([]);
        $promiseData[Store::STATE] = Client::FULFILLED;
        $promiseData[Store::RESULT] = 'test_result_success_fulfill';
        $this->object->update($promiseData);

        $promise = new Client($this->store, $promiseData[Store::ID]);
        $result = $promise->wait(false);
        $this->assertEquals(
                'test_result_success_fulfill', $result
        );
        $this->assertEquals(
                Client::FULFILLED, $promise->getState()
        );

        $promiseData[Store::STATE] = Client::REJECTED;
        $this->setExpectedExceptionRegExp('\zaboy\rest\DataStore\DataStoreException');
        $this->object->update($promiseData);
    }

}