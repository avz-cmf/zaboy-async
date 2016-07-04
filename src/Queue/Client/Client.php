<?php

namespace zaboy\async\Queue\Client;

use ReputationVIP\QueueClient\Adapter\AdapterInterface;
use ReputationVIP\QueueClient\QueueClient;
use zaboy\rest\DataStore\Interfaces\ReadInterface;
use zaboy\async\Queue\Adapter\DataStoresAbstract;

/**
 *
 * <code>
 * $message = [
 *     'id' => '1_ManagedQueue11__576522deb5ad08'
 *     'Body' => mix
 *     'priority' => 'HIGH'
 *     'time-in-flight' => 1466245854
 * ]
 *  </code>
 *
 * @category   async
 * @package    zaboy
 */
class Client extends QueueClient
{

    const MESSAGE_ID = ReadInterface::DEF_ID;
    const BODY = 'Body';
    const PRIORITY = 'priority';
    const TIME_IN_FLIGHT = 'time-in-flight';

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(DataStoresAbstract $adapter)
    {
        parent::__construct($adapter);
    }

    /**
     * Returns the adapter
     *
     * I have no idea why,
     * but ReputationVIP\QueueClient\QueueClient
     * have not got the method getAdapter().
     * We fix it.
     *
     * @see \ReputationVIP\QueueClient\QueueClient
     * @return \zaboy\async\Queue\Adapter\DataStoresAbstract
     */
    public function getAdapter()
    {
        $reflection = new \ReflectionClass('\ReputationVIP\QueueClient\QueueClient');
        $adapterProperty = $reflection->getProperty('adapter');
        $adapterProperty->setAccessible(true);
        $adapter = $adapterProperty->getValue($this);
        $adapterProperty->setAccessible(false);
        return $adapter;
    }

}
