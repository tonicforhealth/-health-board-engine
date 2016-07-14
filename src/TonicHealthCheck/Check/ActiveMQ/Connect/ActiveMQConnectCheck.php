<?php

namespace TonicHealthCheck\Check\ActiveMQ\Connect;

use Stomp\StatefulStomp as StatefulStomp;
use Stomp\Exception\StompException;
use TonicHealthCheck\Check\ActiveMQ\AbstractActiveMQCheck;

/**
 * Class ActiveMQConnectCheck.
 */
class ActiveMQConnectCheck extends AbstractActiveMQCheck
{
    const CHECK = 'activemq-connect-check';

    const TEST_DESTINATION = '/queue/test';
    const TEST_BODY = 'test';
    const TEST_TIME_OUT = 10;

    /**
     * @var StatefulStomp
     */
    protected $statefulStomp;

    /**
     * @param string        $checkNode
     * @param StatefulStomp $statefulStomp
     */
    public function __construct($checkNode, StatefulStomp $statefulStomp)
    {
        parent::__construct($checkNode);
        $this->setStatefulStomp($statefulStomp);
    }

    /**
     * Check PactiveMQClient can to connect to activeMQ server.
     *
     * @throws ActiveMQConnectCheckException
     */
    public function check()
    {
        try {
            $this->getStatefulStomp()->getClient()->connect();
        } catch (StompException $e) {
            throw  ActiveMQConnectCheckException::connectProblem($e);
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
     * @param StatefulStomp $statefulStomp
     */
    protected function setStatefulStomp(StatefulStomp $statefulStomp)
    {
        $this->statefulStomp = $statefulStomp;
    }
}
