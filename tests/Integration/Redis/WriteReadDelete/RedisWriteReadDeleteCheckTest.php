<?php

namespace TonicHealthCheck\Tests\Integration\Redis\WriteReadDelete;

use PHPUnit_Framework_MockObject_MockBuilder;
use Predis\Response\ServerException;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\RedisWriteReadDeleteCheck;
use TonicHealthCheck\Tests\Check\Redis\PredisClientMock;
use TonicHealthCheck\Tests\Integration\Redis\AbstractRedisCheckTest;

/**
 * Class RedisWriteReadDeleteCheckTest.
 */
class RedisWriteReadDeleteCheckTest extends AbstractRedisCheckTest
{
    /**
     * @var RedisWriteReadDeleteCheck
     */
    private $redisWriteReadDeleteCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder
     */
    private $predisClientBuilder;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $this->setPredisClientBuilder($this
            ->getMockBuilder(PredisClientMock::class)
            ->enableProxyingToOriginalMethods());

        $this->setPredisClient($this->getPredisClientBuilder()->getMock());

        $this->setRedisWriteReadDeleteCheck(new RedisWriteReadDeleteCheck(
            'testnode',
            $this->getPredisClient()
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->getChecksList()->add($this->getRedisWriteReadDeleteCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test redis can't write new data.
     */
    public function testRedisCanNotWriteNewData()
    {
        $exceptionMsg = 'Redis Server error';
        $exceptionCode = 2432;

        $this
            ->getPredisClient()
            ->method('set')
            ->willThrowException(
                new ServerException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getRedisWriteReadDeleteCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(3004, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return RedisWriteReadDeleteCheck
     */
    public function getRedisWriteReadDeleteCheck()
    {
        return $this->redisWriteReadDeleteCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getPredisClientBuilder()
    {
        return $this->predisClientBuilder;
    }

    /**
     * @param RedisWriteReadDeleteCheck $redisWriteReadDeleteCheck
     */
    protected function setRedisWriteReadDeleteCheck($redisWriteReadDeleteCheck)
    {
        $this->redisWriteReadDeleteCheck = $redisWriteReadDeleteCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockBuilder $predisClientBuilder
     */
    protected function setPredisClientBuilder($predisClientBuilder)
    {
        $this->predisClientBuilder = $predisClientBuilder;
    }
}
