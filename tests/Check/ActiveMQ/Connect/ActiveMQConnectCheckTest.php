<?php

namespace TonicHealthCheck\Tests\Check\ActiveMQ\Connect;

use Exception;
use Stomp\Client as ClientStomp;
use Stomp\Exception\ConnectionException;
use Stomp\StatefulStomp;
use TonicHealthCheck\Check\ActiveMQ\Connect\ActiveMQConnectCheck;
use TonicHealthCheck\Check\ActiveMQ\Connect\ActiveMQConnectCheckException;
use TonicHealthCheck\Tests\Check\ActiveMQ\AbstractActiveMQCheckTest;

/**
 * Class ActiveMQConnectCheckTest.
 */
class ActiveMQConnectCheckTest extends AbstractActiveMQCheckTest
{
    /**
     * @var ActiveMQConnectCheck
     */
    private $activeMQConnectCheck;

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
            ->enableProxyingToOriginalMethods()
            ->setConstructorArgs([
                $this->getClientStomp(),
            ])
            ->getMock();

        $this->setStatefulStomp($statefulStomp);

        $this->setActiveMQConnectCheck(new ActiveMQConnectCheck(
            'testnode',
            $this->getStatefulStomp()
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $checkResult = $this->getActiveMQConnectCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail connect with exception.
     */
    public function testCheckClientException()
    {
        $exceptionMsg = 'Stomp Client connect error';

        $this
            ->getClientStomp()
            ->method('connect')
            ->willThrowException(
                new ConnectionException(
                    $exceptionMsg
                )
            );

        $checkResult = $this->getActiveMQConnectCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ActiveMQConnectCheckException::CODE_CONNECT_PROBLE, $checkResult->getError()->getCode());
        $this->assertRegExp('#'.$exceptionMsg.'#', $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ActiveMQConnectCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 334
     *
     * Test is fail with unexpected exception
     */
    public function testCheckClientUnexpectedException()
    {
        $exceptionMsg = 'Stomp Client connect error';
        $exceptionCode = 334;

        $this
            ->getClientStomp()
            ->method('connect')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getActiveMQConnectCheck()->performCheck();
    }

    /**
     * @return ActiveMQConnectCheck
     */
    public function getActiveMQConnectCheck()
    {
        return $this->activeMQConnectCheck;
    }

    /**
     * @param ActiveMQConnectCheck $activeMQConnectCheck
     */
    protected function setActiveMQConnectCheck(ActiveMQConnectCheck $activeMQConnectCheck)
    {
        $this->activeMQConnectCheck = $activeMQConnectCheck;
    }
}
