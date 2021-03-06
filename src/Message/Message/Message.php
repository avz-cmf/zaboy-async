<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Message\Message;

use zaboy\async\Message\Interfaces\MessageInterface;
use zaboy\async\Message\MessageException;
use zaboy\async\Message\Store;
use zaboy\async\Message\Message\FulfilledMessage;
use zaboy\async\Message\Message\RejectedMessage;
use zaboy\async\EntityAbstract;

/**
 * Message
 *
 * @category   async
 * @package    zaboy
 */
class Message extends EntityAbstract
{

    //message priority levels
    const HIGH = 1;
    const NORM = 0; //default
    const LOW = -1;

    /**
     *
     * @param Store $data
     */
    public function __construct($data = [])
    {
        $data[Store::PRIORITY] = isset($data[Store::PRIORITY]) ? $data[Store::PRIORITY] : self::NORM;
        parent::__construct($data);
    }

    public function getBody()
    {
        return $this->getData()[Store::MESSAGE_BODY];
    }

    /**
     *
     * @param Store $data
     */
    public function pullMessage()
    {
        $this->data[Store::TIME_IN_FLIGHT] = (int) (time() - date('Z'));
        return $this->getData();
    }

}
