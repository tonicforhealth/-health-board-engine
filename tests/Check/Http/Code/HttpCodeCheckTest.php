<?php

namespace TonicHealthCheck\Tests\Check\Http\Code;

use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
use TonicHealthCheck\Check\Http\Code\HttpCodeCheck;
use TonicHealthCheck\Check\Http\Code\HttpCodeCheckException;
use TonicHealthCheck\Tests\Check\Http\AbstractHttpCheckTest;

/**
 * Class HttpCodeCheckTest.
 */
class HttpCodeCheckTest extends AbstractHttpCheckTest
{
    /**
     * @var HttpCodeCheck
     */
    protected $httpCodeCheck;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();

        $httpMethodsClient = new HttpMethodsClient(
            $this->getMockClient(),
            MessageFactoryDiscovery::find()
        );

        $this->setHttpCodeCheck(new HttpCodeCheck(
            'testnode',
            $httpMethodsClient,
            'http://b2t.tonicforhealth.com/'
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

        $checkResult = $this->getHttpCodeCheck()->performCheck();

        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            HttpCodeCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * test HttpCode.
     */
    public function testCheckHttpCode()
    {
        $response = $this->getMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(404);

        $this->getMockClient()->addResponse($response);

        $checkResult = $this->getHttpCodeCheck()->performCheck();

        $this->assertInstanceOf(
            HttpCodeCheckException::class,
            $checkResult->getError()
        );
        $this->assertEquals(1001, $checkResult->getError()->getCode());
    }

    /**
     * test isOk.
     */
    public function testCheckIsOk()
    {
        $response = $this->getMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(200);

        $this->getMockClient()->addResponse($response);

        $checkResult = $this->getHttpCodeCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * @return HttpCodeCheck
     */
    public function getHttpCodeCheck()
    {
        return $this->httpCodeCheck;
    }

    /**
     * @param HttpCodeCheck $httpCodeCheck
     */
    protected function setHttpCodeCheck(HttpCodeCheck $httpCodeCheck)
    {
        $this->httpCodeCheck = $httpCodeCheck;
    }
}
