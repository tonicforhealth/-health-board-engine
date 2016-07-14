<?php

namespace TonicHealthCheck\Tests\Check\Http\PerformanceDegradation;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ResponseInterface;
use TonicHealthCheck\Check\Http\Code\HttpCodeCheckException;
use TonicHealthCheck\Check\Http\PerformanceDegradation\HttpPerformanceDegradationCheck;
use TonicHealthCheck\Check\Http\PerformanceDegradation\HttpPerformanceDegradationCheckException;
use TonicHealthCheck\Tests\Check\Http\AbstractHttpCheckTest;

/**
 * Class HttpPerformanceDegradationCheckTest.
 */
class HttpPerformanceDegradationCheckTest extends AbstractHttpCheckTest
{
    const TRY_COUNT = 3;

    const AVERAGE_MAX_TIME = 0.02;

    /**
     * @var HttpPerformanceDegradationCheck
     */
    protected $httpPerformanceDegradationCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpMethodsClient;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $httpMethodsClient = $this->getMock(
            HttpMethodsClient::class,
            [],
            [
                $this->getMockClient(),
                MessageFactoryDiscovery::find(),
            ],
            '',
            true,
            true,
            true,
            false,
            true
        );

        $this->setHttpMethodsClient($httpMethodsClient);

        $this->setHttpPerformanceDegradationCheck(new HttpPerformanceDegradationCheck(
            'testnode',
            $this->getHttpMethodsClient(),
            'http://b2t.tonicforhealth.com/',
            static::AVERAGE_MAX_TIME,
            static::TRY_COUNT
        ));
    }

    /**
     * @throws HttpCodeCheckException
     */
    public function testCheckClientExeption()
    {
        $exceptionMsg = 'Connection problem.';
        $exceptionCode = 0;

        $this->getMockClient()->addException(new \Exception($exceptionMsg, $exceptionCode));
        $checkResult = $this->getHttpPerformanceDegradationCheck()->performCheck();

        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            HttpPerformanceDegradationCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * test HttpCode.
     */
    public function testCheckPerformanceDegradation()
    {
        $response = $this->getMock(ResponseInterface::class);

        $this->getHttpMethodsClient()
            ->method('get')
            ->will(
                $this->returnCallback(
                    function () {
                        usleep(static::AVERAGE_MAX_TIME * 1000000);
                    }
                )
            );

        $this->getMockClient()->addResponse($response);

        $checkResult = $this->getHttpPerformanceDegradationCheck()->performCheck();

        $this->assertInstanceOf(
            HttpPerformanceDegradationCheckException::class,
            $checkResult->getError()
        );
        $this->assertEquals(1002, $checkResult->getError()->getCode());
    }

    /**
     * test isOk.
     */
    public function testCheckIsOk()
    {
        $response = $this->getMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(200);

        $this->getMockClient()->addResponse($response);

        $checkResult = $this->getHttpPerformanceDegradationCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * @return HttpPerformanceDegradationCheck
     */
    public function getHttpPerformanceDegradationCheck()
    {
        return $this->httpPerformanceDegradationCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getHttpMethodsClient()
    {
        return $this->httpMethodsClient;
    }

    /**
     * @param HttpPerformanceDegradationCheck $httpPerformanceDegradationCheck
     */
    protected function setHttpPerformanceDegradationCheck(HttpPerformanceDegradationCheck $httpPerformanceDegradationCheck)
    {
        $this->httpPerformanceDegradationCheck = $httpPerformanceDegradationCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $httpMethodsClient
     */
    protected function setHttpMethodsClient($httpMethodsClient)
    {
        $this->httpMethodsClient = $httpMethodsClient;
    }
}
