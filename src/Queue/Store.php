<?php

namespace zaboy\async\Queue;

use zaboy\async\StoreAbstract;
use zaboy\async\Message;
use Zend\Db\Adapter\AdapterInterface;

/**
 * Store for states of  Queue
 *
 * id => queue_id_123456789qwerty
 * creation_time = 2216125; UTC time when it has sarted
 *
 * @category   async
 * @package    zaboy
 */
class Store extends StoreAbstract
{

    //PROMISE_ADAPTER_DATA_STORE
    //
    //'id' - unique id of promise: promise_id_123456789qwerty
    //const ID = ReadInterface::DEF_ID;
    //const NAME = 'name' - table name
    //const CREATION_TIME = 'creation_time';
    //
    const NAME = 'name';

    /**
     *
     * @var Message\Store;
     */
    protected $messagesStore;

    public function __construct($table, AdapterInterface $adapter, Message\Store $messagesStore)
    {
        parent::__construct($table, $adapter);
        $this->messagesStore = $messagesStore;
    }

    public function getMessagesStore()
    {
        return $this->messagesStore;
    }

}
