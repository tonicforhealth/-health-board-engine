<?php

namespace TonicHealthCheck\Tests\Check\ActiveMQ\SendReciveAck;

use Stomp\Exception\StompException;
use Stomp\Network\Connection;
use Stomp\StatefulStomp;
use Stomp\Transport\Frame;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\ActiveMQSendReciveAckCheck;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQReceiveAckCheckException;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQSendAckCheckException;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception\ActiveMQSendReciveAckCheckException;
use TonicHealthCheck\Tests\Check\ActiveMQ\AbstractActiveMQCheckTest;
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

        $checkResult = $this->getActiveMQSendReciveAckCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail send.
     */
    public function testCheckIsFailSendException()
    {
        $exceptionMsg = 'Stomp Client/Server error';
        $exceptionCode = 1741;

        $this
            ->getStatefulStomp()
            ->method('send')
            ->willThrowException(
                new StompException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getActiveMQSendReciveAckCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ActiveMQSendAckCheckException::CODE_CAN_NOT_SENT, $checkResult->getError()->getCode());
        $this->assertRegExp('#'.$exceptionMsg.'#', $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ActiveMQSendAckCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail receive.
     */
    public function testCheckIsFailReceiveException()
    {
        $this->setUpStatefulStomp();
        $exceptionMsg = 'Stomp Client/Server error';
        $exceptionCode = 1853;

        $this
            ->getStatefulStomp()
            ->method('read')
            ->willThrowException(
                new StompException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getActiveMQSendReciveAckCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ActiveMQReceiveAckCheckException::CODE_CAN_NOT_RECIVE, $checkResult->getError()->getCode());
        $this->assertRegExp('#'.$exceptionMsg.'#', $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ActiveMQReceiveAckCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is receive return false.
     */
    public function testCheckIsFailReceiveReturnFalse()
    {
        $this->setUpStatefulStomp();

        $this
            ->getStatefulStomp()
            ->method('read')
            ->willReturn(
                false
            );

        $checkResult = $this->getActiveMQSendReciveAckCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ActiveMQReceiveAckCheckException::CODE_CAN_NOT_RECIVE, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            ActiveMQReceiveAckCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail subscribe.
     */
    public function testCheckIsFailSubscribeException()
    {
        $this->setUpStatefulStomp();
        $exceptionMsg = 'Stomp Client/Server error';
        $exceptionCode = 1853;

        $this
            ->getStatefulStomp()
            ->method('subscribe')
            ->willThrowException(
                new StompException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getActiveMQSendReciveAckCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ActiveMQSendReciveAckCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertRegExp('#'.$exceptionMsg.'#', $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ActiveMQSendReciveAckCheckException::class,
            $checkResult->getError()
        );
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
