<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Promise\Promise;

use zaboy\async\Promise\Promise\DeterminedPromise;
use zaboy\async\Promise\Promise\DependentPromise;
use zaboy\async\Promise\PromiseException;
use zaboy\async\Promise\Store;
use zaboy\async\Promise\Interfaces\PromiseInterface;

/**
 * FulfilledPromise
 *
 * @category   async
 * @package    zaboy
 */
class FulfilledPromise extends DeterminedPromise
{

    /**
     *
     * @param Store $store
     * @throws PromiseException
     */
    public function __construct($promiseData = [], $result = null)
    {
        parent::__construct($promiseData);
        $this->data[Store::STATE] = PromiseInterface::FULFILLED;
        if (!isset($this->data[Store::RESULT]) && !is_null($result)) {
            $this->data[Store::RESULT] = $this->serializeResult($result);
        }
    }

    public function getState()
    {
        return PromiseInterface::FULFILLED;
    }

    public function resolve($value)
    {
        if ($value != $this->data[Store::RESULT]) {
            throw new PromiseException('Promise is already resolved.  Promise: ' . $this->data[Store::ID]);
        }
        return $this->data;
    }

    public function reject($reason)
    {
        throw new PromiseException('Cannot reject a fulfilled promise.  Promise: ' . $this->data[Store::ID]);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise([], $this->getId(), $onFulfilled, $onRejected);
        $result = $this->wait(false);
        $promiseData = $dependentPromise->resolve($result);
        return $promiseData;
    }

}
