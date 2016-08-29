<?php

namespace zaboy\test\async\Callback;

use zaboy\async\Promise\Store as PromiseStore;
use Interop\Container\ContainerInterface;
use zaboy\async\Callback\Interrupter\Script;
use zaboy\async\Callback\AsyncCallback;
use zaboy\test\async\Callback\Example\JustCallable;

class ScriptInterruptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var PromiseStore
     */
    protected $promiseStore;

    /**
     * @var Script
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed
     */
    protected function setUp()
    {
        global $testCase;
        $testCase = 'table_for_test';
        $this->container = include './config/container.php';

        $this->promiseStore = AsyncCallback::setContaner($this->container);
        $this->object = (new Script())->setScriptName(__DIR__ . '/Example/scripts/testScriptProxy.php');
    }

    /* ---------------------------------------------------------------------------------- */

    public function test_beforeSerializePromiseAfterSleep__Invoke()
    {

        $callable = new JustCallable();
        $callback = new AsyncCallback([$callable, 'callReturnPromise'], $this->object);
        $promise = call_user_func($callback, 'paramForCall');

        sleep(2);

        $this->assertEquals(
            "fulfilled", $promise->getState()
        );
        $this->assertEquals(
            "JustCallable::callReturnPromise resolve paramForCall", $promise->wait(false)
        );
    }
}