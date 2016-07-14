<?php

namespace TonicHealthCheck\tests\Integration\Http\Code;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\ResponseInterface;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Http\Code\HttpCodeCheck;
use TonicHealthCheck\Tests\Integration\Http\AbstractHttpCheckTest;

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
            'http://localhost/'
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

        $this->getChecksList()->add($this->getHttpCodeCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * test http server is down.
     */
    public function testCheckHttpServerIsDown()
    {
        $exceptionMsg = 'cURL error 52: Empty reply from server';

        $this->getMockClient()->addException(
            new RequestException(
                $exceptionMsg,
                $this->getMockBuilder(RequestInterface::class)->getMock()
            )
        );

        $this->getChecksList()->add($this->getHttpCodeCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(-1, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * test http server is frozen.
     */
    public function testCheckHttpServerIsFrozen()
    {
        $exceptionMsg = 'cURL error 28: Operation timed out after 10003 milliseconds with 0 bytes received';

        $this->getMockClient()->addException(
            new RequestException(
                $exceptionMsg,
                $this->getMockBuilder(RequestInterface::class)->getMock()
            )
        );

        $this->getChecksList()->add($this->getHttpCodeCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(-1, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * test http server makes responses very slowly.
     */
    public function testCheckHttpServerIsVerySlow()
    {
        $exceptionMsg = 'cURL error 28: Operation timed out after 10001 milliseconds with 0 bytes received';

        $this->getMockClient()->addException(
            new RequestException(
                $exceptionMsg,
                $this->getMockBuilder(RequestInterface::class)->getMock()
            )
        );

        $this->getChecksList()->add($this->getHttpCodeCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(-1, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * test http server has a corrupted response code 500, 404, etc.
     *
     * @dataProvider getCorruptedResponseCodes
     */
    public function testCheckHttpServerHasCorruptedResponseCode($code)
    {
        $response = $this->getMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn($code);

        $this->getMockClient()->addResponse($response);

        $this->getChecksList()->add($this->getHttpCodeCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($code) {
                $this->assertEquals(1001, $exception->getCode());
                $this->assertContains(
                    sprintf('CODE:%d expected CODE:200', $code),
                    $exception->getMessage()
                );

                return true;
            }
        );

        $this->performChecks();
    }
    /**
     * get list of the corrupted response code.
     */
    public function getCorruptedResponseCodes()
    {
        return [
            [404],
            [500],
            [501],
        ];
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
