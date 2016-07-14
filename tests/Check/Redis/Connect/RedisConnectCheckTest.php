<?php

namespace TonicHealthCheck\Tests\Check\Redis\Connect;

use Exception;
use Predis\Connection\ConnectionException;
use Predis\Connection\NodeConnectionInterface;
use TonicHealthCheck\Check\Redis\Connect\RedisConnectCheck;
use TonicHealthCheck\Check\Redis\Connect\RedisConnectCheckException;
use TonicHealthCheck\Tests\Check\Redis\AbstractRedisCheckTest;
use TonicHealthCheck\Tests\Check\Redis\PredisClientMock;

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
        $checkResult = $this->getRedisConnectCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail with exception.
     */
    public function testCheckClientException()
    {
        $exceptionMsg = 'Redis Client connect error';
        $exceptionCode = 34334;

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

        $checkResult = $this->getRedisConnectCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(RedisConnectCheckException::CODE_CONNECT_PROBLE, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            RedisConnectCheckException::class,
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
        $exceptionMsg = 'Redis Client connect error';
        $exceptionCode = 334;

        $this
            ->getPredisClient()
            ->method('connect')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getRedisConnectCheck()->performCheck();
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
