<?php

namespace TonicHealthCheck\Tests\Integration\ActiveMQ\Connect;

use Stomp\Client as ClientStomp;
use Stomp\Exception\ConnectionException;
use Stomp\StatefulStomp;
use TonicHealthCheck\Check\ActiveMQ\Connect\ActiveMQConnectCheck;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Tests\Integration\ActiveMQ\AbstractActiveMQCheckTest;

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
        parent::setUp();
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
        $this->getChecksList()->add($this->getActiveMQConnectCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test activemq is down
     * Test activemq is frozen (the same).
     */
    public function testActivemqIsDown()
    {
        $exceptionMsg = 'Was not possible to read data from stream. (Host: localhost)';

        $this
            ->getClientStomp()
            ->method('connect')
            ->willThrowException(
                new ConnectionException(
                    $exceptionMsg
                )
            );

        $this->getChecksList()->add($this->getActiveMQConnectCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(4001, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
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
