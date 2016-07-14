<?php

namespace TonicHealthCheck\Tests\Check\Redis\WriteReadDelete;

use Exception;
use PHPUnit_Framework_MockObject_MockBuilder;
use Predis\Response\ServerException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisDeleteCheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisWRDCheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisWriteReadCheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\RedisWriteReadDeleteCheck;
use TonicHealthCheck\Tests\Check\Redis\AbstractRedisCheckTest;
use TonicHealthCheck\Tests\Check\Redis\PredisClientMock;

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
        $checkResult = $this->getRedisWriteReadDeleteCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail set unexpected working.
     */
    public function testCheckIsFailSet()
    {
        $predisClient = $this->getPredisClientBuilder()->setMethods(['set'])->getMock();

        $redisWriteReadDeleteCheck = new RedisWriteReadDeleteCheck(
            'testnode',
            $predisClient
        );

        $checkResult = $redisWriteReadDeleteCheck->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(RedisWriteReadCheckException::CODE_DOES_NOT_SAVE, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            RedisWriteReadCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail set unexpected working 2.
     */
    public function testCheckIsFailReset()
    {
        $predisClient = $this->getPredisClient()->setResetUnexpectedOn();

        $checkResult = $this->getRedisWriteReadDeleteCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(RedisWriteReadCheckException::CODE_DOES_NOT_SAVE, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            RedisWriteReadCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail delete.
     */
    public function testCheckIsFailDel()
    {
        $predisClient = $this->getPredisClientBuilder()->setMethods(['del'])->getMock();

        $redisWriteReadDeleteCheck = new RedisWriteReadDeleteCheck(
            'testnode',
            $predisClient
        );

        $checkResult = $redisWriteReadDeleteCheck->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(RedisDeleteCheckException::CODE_DOES_NOT_DELETE, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            RedisDeleteCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail with exception.
     */
    public function testCheckClientException()
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

        $checkResult = $this->getRedisWriteReadDeleteCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(RedisWRDCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            RedisWRDCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 24325
     *
     * Test is fail with unexpected exception
     */
    public function testCheckClientUnexpectedException()
    {
        $exceptionMsg = 'Redis Server error';
        $exceptionCode = 24325;

        $this
            ->getPredisClient()
            ->method('set')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getRedisWriteReadDeleteCheck()->performCheck();
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
