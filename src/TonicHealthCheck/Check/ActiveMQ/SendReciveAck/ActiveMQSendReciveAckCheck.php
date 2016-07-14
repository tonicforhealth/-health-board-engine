<?php

namespace TonicHealthCheck\Check\ActiveMQ\SendReciveAck;

use Stomp\StatefulStomp as StatefulStomp;
use Stomp\Transport\Message;
use Stomp\Exception\StompException;
use TonicHealthCheck\Check\ActiveMQ\AbstractActiveMQCheck;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQReceiveAckCheckException;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQSendAckCheckException;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQSendReciveAckCheckException;

/**
 * Class ActiveMQSendReciveAckCheck.
 */
class ActiveMQSendReciveAckCheck extends AbstractActiveMQCheck
{
    const CHECK = 'activemq-send-recive-ack-check';

    const TEST_DESTINATION = '/queue/test';
    const TEST_BODY = 'The story is about a plain conjurer and a starship captain. It takes place in a galaxy-spanning theocracy.';
    const TEST_TIME_OUT = 10;

    /**
     * @var StatefulStomp
     */
    private $statefulStomp;

    /**
     * @var string
     */
    private $destination = self::TEST_DESTINATION;

    /**
     * @var string
     */
    private $body = self::TEST_BODY;

    /**
     * @var int
     */
    private $timeOut = self::TEST_TIME_OUT;

    /**
     * ActiveMQSendReciveAckCheck constructor.
     *
     * @param null          $checkNode
     * @param StatefulStomp $statefulStomp
     * @param string        $destination
     * @param string        $body
     * @param int           $timeOut
     */
    public function __construct(
        $checkNode,
        StatefulStomp $statefulStomp,
        $destination = null,
        $body = null,
        $timeOut = null
    ) {
        parent::__construct($checkNode);
        $this->setStatefulStomp($statefulStomp);

        if (null !== $destination) {
            $this->setDestination($destination);
        }

        if (null !== $body) {
            $this->setBody($body);
        }

        if (null !== $timeOut) {
            $this->setTimeOut($timeOut);
        }
    }

    /**
     * test activeMQ send&recive&acknowledgment.
     *
     * @throws ActiveMQReceiveAckCheckException
     * @throws ActiveMQSendAckCheckException
     * @throws ActiveMQSendReciveAckCheckException
     */
    public function check()
    {
        try {
            $message = new Message($this->getBody());
            try {
                $this->getStatefulStomp()->send($this->getDestination(), $message);
            } catch (StompException $e) {
                throw ActiveMQSendAckCheckException::canNotSent($this->getDestination(), $this->getBody(), $e);
            }
            if (count($this->getStatefulStomp()->getSubscriptions()) > 0) {
                $this->getStatefulStomp()->unsubscribe();
            }
            $this->getStatefulStomp()->subscribe($this->getDestination(), null, 'client-individual');
            $this->getStatefulStomp()->getClient()->getConnection()->setReadTimeout($this->getTimeOut() / 2);
            $this->getStatefulStomp()->getClient()->setReceiptWait($this->getTimeOut());

            try {
                $message = $this->getStatefulStomp()->read();
            } catch (StompException $e) {
                throw ActiveMQReceiveAckCheckException::canNotReceive($this->getDestination(), $e);
            }

            if ($message !== false) {
                $this->getStatefulStomp()->ack($message);
            } else {
                throw ActiveMQReceiveAckCheckException::canNotReceive($this->getDestination(), new \Exception('Message didn\'t receive'));
            }

            $this->getStatefulStomp()->unsubscribe();
        } catch (StompException $e) {
            throw ActiveMQSendReciveAckCheckException::internalProblem($e);
        }
    }

    /**
     * @return StatefulStomp
     */
    public function getStatefulStomp()
    {
        return $this->statefulStomp;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @param StatefulStomp $statefulStomp
     */
    protected function setStatefulStomp(StatefulStomp $statefulStomp)
    {
        $this->statefulStomp = $statefulStomp;
    }

    /**
     * @param string $destination
     */
    protected function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @param string $body
     */
    protected function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @param int $timeOut
     */
    protected function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
    }
}
