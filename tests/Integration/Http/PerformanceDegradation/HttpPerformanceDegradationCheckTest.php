<?php

namespace TonicHealthCheck\Tests\Integration\Http\PerformanceDegradation;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Http\Message\ResponseInterface;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Http\PerformanceDegradation\HttpPerformanceDegradationCheck;
use TonicHealthCheck\Tests\Integration\Http\AbstractHttpCheckTest;

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
            'http://localhost/',
            static::AVERAGE_MAX_TIME,
            static::TRY_COUNT
        ));
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

        $this->getChecksList()->add($this->getHttpPerformanceDegradationCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * test http server makes responses very slowly.
     */
    public function testCheckHttpServerIsVerySlow()
    {
        $response = $this->getMock(ResponseInterface::class);

        $this->getHttpMethodsClient()
            ->method('get')
            ->will(
                $this->returnCallback(
                    function () {
                        usleep(static::AVERAGE_MAX_TIME * 2000000);
                    }
                )
            );

        $this->getMockClient()->addResponse($response);

        $this->getChecksList()->add($this->getHttpPerformanceDegradationCheck());

        $this->expectFireIncident(
            function (CheckException $exception) {
                $this->assertEquals(1002, $exception->getCode());
                $this->assertContains('Current HTTP responce average time', $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
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
