<?php

namespace zaboy\async\Queue\Adapter;

use ReputationVIP\QueueClient\QueueClientInterface;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;
use zaboy\async\Queue\PriorityHandler\PriorityHandler;
use zaboy\async\Queue\QueueException;
use ReputationVIP\QueueClient\Adapter\AdapterInterface;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Node\Query\LogicOperator;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node;
use Xiag\Rql\Parser\DataType\Glob;

/**
 *
 * @category   async
 * @package    zaboy
 */
class DataStores extends DataStoresAbstract implements AdapterInterface
{

    /**
     *
     * @param DataStoresInterface $queuesDataStore
     * @param DataStoresInterface $messagesDataStore
     * @throws QueueException
     */
    public function __construct(DataStoresInterface $queuesDataStore, DataStoresInterface $messagesDataStore)
    {
        if (is_null($queuesDataStore) || is_null($messagesDataStore)) {
            throw new QueueException('Argument is not defined.');
        }

        $this->queuesDataStore = $queuesDataStore;
        $this->messagesDataStore = $messagesDataStore;
        $this->priorityHandler = new PriorityHandler();
        $this->maxTimeInFlight = self::DEFAULT_MAX_TIME_IN_FLIGHT_VALUE;
    }

    /**
     * @inheritdoc
     */
    public function addMessage($queueName, $message, $priority = null)
    {

        if (empty($queueName)) {
            throw new QueueException('The parameter "queueName" is empty or not defined.');
        }
        if (!in_array($queueName, $this->listQueues())) {
            throw new QueueException('The queue with name "' . $queueName . '" does not exist.');
        }
        if (empty($message)) {
            throw new QueueException('The parameter "message" is empty or not defined.');
        }
        $identifier = $this->messagesDataStore->getIdentifier();
        $idWithDot = uniqid(
                '0' . self::ID_SEPARATOR
                . $queueName . self::ID_SEPARATOR
                . $priority . self::ID_SEPARATOR
                , true
        );
        $id = str_replace('.', self::ID_SEPARATOR, $idWithDot);
        $priorityIndex = $this->getPriorityIndex($priority);
        $new_message = [
            $identifier => $id,
            self::QUEUE_NAME => $queueName,
            self::MESSAGE_BODY => serialize($message),
            self::PRIORITY => $priorityIndex,
            self::TIME_IN_FLIGHT => 0,
            self::CREATED_ON => time(),
        ];
        $this->messagesDataStore->create($new_message);
        return $this;
    }

    /**
     * @param string $queueName
     * @param array  $messages
     * @param string $priority
     *
     * @return QueueClientInterface
     */
    public function addMessages($queueName, $messages, $priority = null)
    {
        foreach ($messages as $message) {
            $this->addMessage($queueName, $message, $priority);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessages($queueName, $numberMsg = 1, $priority = null)
    {
        $messages = [];
        for ($i = 0; $i < $numberMsg; $i++) {
            $message = $this->getMessage($queueName, $priority);
            if (!is_null($message)) {
                $messages[] = $message;
            } else {
                return $messages;
            }
        }
        return $messages;
    }

    /**
     * @inheritdoc
     */
    public function deleteMessage($queueName, $message)
    {
        $identifier = $this->messagesDataStore->getIdentifier();
        if (!isset($message[$identifier])) {
            throw new QueueException('The message identifier is not found in the message.');
        }
        $this->messagesDataStore->delete($message[$identifier]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($queueName, $priority = null)
    {
        $query = new Query();
        $scalarNodeQueue = new ScalarOperator\EqNode(
                self::QUEUE_NAME, $queueName
        );
        if (isset($priority)) {
            $scalarNodePriority = new ScalarOperator\EqNode(
                    self::PRIORITY, $this->getPriorityIndex($priority)
            );
            $node = new LogicOperator\AndNode([
                $scalarNodePriority,
                $scalarNodeQueue
            ]);
        } else {
            $node = $scalarNodeQueue;
        }
        $query->setQuery($node);
        $selectNode = new Node\SelectNode([self::PRIORITY]);
        $query->setSelect($selectNode);
        $messages = $this->messagesDataStore->query($query);
        return count($messages) > 0 ? false : true;
    }

    /**
     * @inheritdoc
     */
    public function getNumberMessages($queueName, $priority = null)
    {
        $query = new Query();
        $scalarNodeQueue = new ScalarOperator\EqNode(
                self::QUEUE_NAME, $queueName
        );
        $scalarNodeNotInFlight = new ScalarOperator\EqNode(
                self::TIME_IN_FLIGHT, 0
        );
        $scalarNodeLongnInFlight = new ScalarOperator\LtNode(
                self::TIME_IN_FLIGHT, time() - $this->getMaxTimeInFlight()
        );
        $orNodeInFlight = new LogicOperator\OrNode([
            $scalarNodeNotInFlight,
            $scalarNodeLongnInFlight
        ]);
        if (isset($priority)) {
            $scalarNodePriority = new ScalarOperator\EqNode(
                    self::PRIORITY, $this->getPriorityIndex($priority)
            );
            $andNode = new LogicOperator\AndNode([
                $orNodeInFlight,
                $scalarNodePriority,
                $scalarNodeQueue
            ]);
        } else {
            $andNode = new LogicOperator\AndNode([
                $orNodeInFlight,
                $scalarNodeQueue
            ]);
        }
        $query->setQuery($andNode);
        $selectNode = new Node\SelectNode([self::PRIORITY]);
        $query->setSelect($selectNode);
        $messages = $this->messagesDataStore->query($query);
        return count($messages);
    }

    /**
     * @inheritdoc
     */
    public function deleteQueue($queueName)
    {
        $this->queuesDataStore->delete($queueName);
        $this->purgeQueue($queueName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createQueue($queueName)
    {
        $identifier = $this->queuesDataStore->getIdentifier();
        $itemData = [$identifier => $queueName];
        $this->queuesDataStore->create($itemData, true);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function renameQueue($sourceQueueName, $targetQueueName)
    {
        $this->deleteQueue($sourceQueueName);
        $this->createQueue($targetQueueName);
        $priorities = $this->priorityHandler->getAll();
        foreach ($priorities as $priority) {
            while (count($messages = $this->getMessages($sourceQueueName, 1, $priority)) > 0) {
                $this->deleteMessage($sourceQueueName, $messages[0]);
                array_walk($messages, function (&$item) {
                    $item = $item['Body'];
                });
                $this->addMessage($targetQueueName, $messages[0], $priority);
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function purgeQueue($queueName, $priority = null)
    {
        $identifier = $this->messagesDataStore->getIdentifier();
        $messagesWithIdOnly = $this->readAllMessagesIdFromQueue($queueName, $priority);
        foreach ($messagesWithIdOnly as $message) {
            $id = $message[$identifier];
            $this->messagesDataStore->delete($id);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function listQueues($prefix = '')
    {
        $identifier = $this->messagesDataStore->getIdentifier();
        $query = new Query();
        if ($prefix !== '') {
            $likeNode = new ScalarOperator\LikeNode(
                    $identifier, new Glob($prefix . '*')
            );
            $query->setQuery($likeNode);
        }
        $queues = $this->queuesDataStore->query($query);
        $result = [];
        foreach ($queues as $queue) {
            $id = $queue[$identifier];
            $result[] = $id;
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPriorityHandler()
    {
        return $this->priorityHandler;
    }

//    // for the last commented test
//    protected function readMessageFifoButNewerFlightInFirst($queueName, $priority = null, $attemptNumber = 1)
//    {
//        $messages = parent::readMessageFifoButNewerFlightInFirst($queueName, $priority, $attemptNumber);
//        if ($this->getMaxTimeInFlight() !== 5) {
//            $this->setMaxTimeInFlight(5);
//            $messages2 = $this->getMessages($queueName, 7, 'HIGH');
//
//            foreach ($messages2 as $val) {
//
//            }
//        } else {
//            foreach ($messages as $val) {
//
//            }
//        }
//
//        return $messages;
//    }
}