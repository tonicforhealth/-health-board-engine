<?php

namespace TonicHealthCheck\Tests\Integration\ActiveMQ\SendReciveAck;

use Stomp\Network\Connection;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\ActiveMQSendReciveAckCheck;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQReceiveAckCheckException;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Tests\Integration\ActiveMQ\AbstractActiveMQCheckTest;
use Stomp\Client as ClientStomp;

/**
 * Class ActiveMQSendReciveAckCheckTest.
 */
class ActiveMQSendReciveAckCheckTest extends AbstractActiveMQCheckTest
{
    /**
     * @var ActiveMQSendReciveAckCheck
     */
    private $activeMQSendReciveAckCheck;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $clientStomp = $this
            ->getMockBuilder(ClientStomp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setClientStomp($clientStomp);

        $statefulStomp = $this
            ->getMockBuilder(StatefulStomp::class)
            ->setConstructorArgs([
                $this->getClientStomp(),
            ])
            ->getMock();

        $this->setStatefulStomp($statefulStomp);

        $this->setActiveMQSendReciveAckCheck(new ActiveMQSendReciveAckCheck(
            'testnode',
            $this->getStatefulStomp(),
            ActiveMQSendReciveAckCheck::TEST_DESTINATION,
            ActiveMQSendReciveAckCheck::TEST_BODY,
            ActiveMQSendReciveAckCheck::TEST_TIME_OUT
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->setUpStatefulStomp();

        $this->getStatefulStomp()
            ->method('read')
            ->willReturn(
                $this
                    ->getMockBuilder(Frame::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        $this->getStatefulStomp()
           ->method('getSubscriptions')
           ->willReturn(
               [
                   1 => 'massage 1',
                   2 => 'massage 2',
                   3 => 'massage 3',
                   4 => 'massage 4',
                   5 => 'massage 5',
               ]
           );

        $this->getChecksList()->add($this->getActiveMQSendReciveAckCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test activemq is slow.
     */
    public function testActivemqIsSlow()
    {
        $this->setUpStatefulStomp();
        $exceptionMsg = 'Message didn\'t receive';

        $this
            ->getStatefulStomp()
            ->method('read')
            ->willThrowException(
                ActiveMQReceiveAckCheckException::canNotReceive(
                    '/queue/test',
                    new \Exception('Message didn\'t receive')
                )
            );

        $this->getChecksList()->add($this->getActiveMQSendReciveAckCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(4003, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return ActiveMQSendReciveAckCheck
     */
    public function getActiveMQSendReciveAckCheck()
    {
        return $this->activeMQSendReciveAckCheck;
    }

    /**
     * @param ActiveMQSendReciveAckCheck $activeMQSendReciveAckCheck
     */
    protected function setActiveMQSendReciveAckCheck(ActiveMQSendReciveAckCheck $activeMQSendReciveAckCheck)
    {
        $this->activeMQSendReciveAckCheck = $activeMQSendReciveAckCheck;
    }

    /**
     * Set up base mock method for StatefulStomp.
     */
    protected function setUpStatefulStomp()
    {
        $this
            ->getClientStomp()
            ->method('getConnection')
            ->willReturn(
                $this
                    ->getMockBuilder(Connection::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        $this->getStatefulStomp()
            ->method('getClient')
            ->willReturn($this->getClientStomp());
    }
}
