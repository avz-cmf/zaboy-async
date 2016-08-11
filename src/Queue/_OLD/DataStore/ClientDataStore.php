<?php

namespace zaboy\async\Queue\DataStore;

use zaboy\rest\DataStore;
use zaboy\rest\DataStore\DataStoreAbstract;
use zaboy\async\Queue\Client\Client;
use zaboy\async\Queue\QueueException;
use Xiag\Rql\Parser\Query;
use zaboy\async\Queue\Broker\QueueBrokerInterface;

/**
 *
 * <code>
 *   'services' => [
 *       'abstract_factories' => [
 *           'zaboy\async\Queue\Factory\DataStore\ClientDataStoreAbstractFactory'
 *       ]
 *   ],
 *   'dataStore' => [
 *       'test_ClientDataStore' => [
 *           'queueClient' => 'testMysqlQueue'
 *           '
 *       ]
 *   ]
 * <code>
 *
 * @category   async
 * @package    zaboy
 */
class ClientDataStore extends DataStore\DataStoreAbstract
{

    /**
     *
     * @var \zaboy\async\Queue\Client\Client
     */
    protected $queueClient;

    /**
     *
     * @var QueueBrokerInterface
     */
    protected $queueBroker;

    /**
     *
     * @var DataStoreAbstract
     */
    protected $queuesDataStore;

    public function __construct(Client $queueClient, QueueBrokerInterface $queueBroker = null)
    {
        $this->queueClient = $queueClient;
        $this->queuesDataStore = $queueClient->getAdapter()->getQueuesDataStore();
        $this->queueBroker = $queueBroker;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function read($id)
    {
        return $this->queuesDataStore->read($id);
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function query(Query $query)
    {
        return $this->queuesDataStore->query($query);
    }

// ** Interface "/Coutable"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return $this->queuesDataStore->count();
    }

// ** Interface "/IteratorAggregate"  **/

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return $this->queuesDataStore->getIterator();
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return Client::MESSAGE_ID;
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function update($itemData, $createIfAbsent = false)
    {
        $this->throwException('update');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function create($itemData, $rewriteIfExist = false)
    {
        $this->throwException('create');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $this->throwException('delete');
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function deleteAll()
    {
        $this->throwException('deleteAll');
    }

    public function getQueueClient()
    {
        return $this->queueClient;
    }

    /**
     * @param $methodName
     * @throws QueueException
     */
    protected function throwException($methodName)
    {
        throw new QueueException(
        'The DataStore type zaboy\async\Queue\Client\Client doesn\'t allow to work with method: ' . $methodName
        );
    }

}