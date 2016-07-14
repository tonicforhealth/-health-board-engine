<?php

namespace TonicHealthCheck\Tests\Integration\Redis\Connect;

use Predis\Connection\ConnectionException;
use Predis\Connection\NodeConnectionInterface;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Redis\Connect\RedisConnectCheck;
use TonicHealthCheck\Tests\Check\Redis\PredisClientMock;
use TonicHealthCheck\Tests\Integration\Redis\AbstractRedisCheckTest;

/**
 * Class RedisConnectCheckTest.
 */
class RedisConnectCheckTest extends AbstractRedisCheckTest
{
    /**
     * @var RedisConnectCheck
     */
    private $redisConnectCheck;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $predisClient = $this
            ->getMockBuilder(PredisClientMock::class)
            ->getMock();

        $this->setPredisClient($predisClient);

        $this->setRedisConnectCheck(new RedisConnectCheck(
            'testnode',
            $this->getPredisClient()
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->getChecksList()->add($this->getRedisConnectCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test redis is down
     * Test redis is frozen (the same).
     */
    public function testRedisIsDown()
    {
        $exceptionMsg = 'Error while reading line from the server. [tcp://localhost:6379]';
        $exceptionCode = 0;

        $this
            ->getPredisClient()
            ->method('connect')
            ->willThrowException(
                new ConnectionException(
                    $this->getMock(NodeConnectionInterface::class),
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getRedisConnectCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(3001, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return RedisConnectCheck
     */
    public function getRedisConnectCheck()
    {
        return $this->redisConnectCheck;
    }

    /**
     * @param RedisConnectCheck $redisConnectCheck
     */
    protected function setRedisConnectCheck($redisConnectCheck)
    {
        $this->redisConnectCheck = $redisConnectCheck;
    }
}
