<?php

namespace TonicHealthCheck\Tests\System\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseInterface;
use Http\Mock\Client as MockClient;
use TonicHealthCheck\Tests\Integration\System\AbstractSystemTest;

/**
 * Class AbstractHttpCheckTest.
 */
class HttpCheckTest extends AbstractSystemTest
{
    /**
     * @var MockClient
     */
    private $mockClient;
    /**
     * @var GuzzleHttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private $guzzleHttpClientMock;

    /**
     * Set up HttpIntegrationMock.
     */
    public function setUp()
    {
        parent::setUp();

        $this->guzzleHttpClientMock = $this
            ->getMockBuilder(GuzzleHttpClient::class)
            ->enableProxyingToOriginalMethods()
            ->setMethods(['send'])
            ->getMock();
    }
    /**
     * Test is check ok.
     */
    public function testCheckIsFail()
    {
        $this->getContainer()->offsetSet('guzzle_http_client_callback', $this->getMockClientCallback());
        $leftIndents = ['local.web.http.http-code-check', 'local.web.http.http-performance-degradation-check'];
        $this->filterCheckList($leftIndents);

        $responseMock = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $this->getGuzzleHttpClientMock()
            ->method('send')
            ->willReturn($responseMock);

        $this->getOutputInterfaceMock()
            ->expects($this->at(0))
            ->method('writeln')
            ->with(
                $this->stringContains('<fg=green;options=bold>local.web.http.http-code-check: NT: TEXT_HTTP_START_CHECK</>')
            );

        $this->getOutputInterfaceMock()
            ->expects($this->at(1))
            ->method('writeln')
            ->with(
                $this->stringContains('CODE:-1')
            );

        $this->performChecks();

    }

    public function dtestHttpServerIsDown()
    {
    }

    /**
     * @return MockClient
     */
    public function getMockClient()
    {
        return $this->mockClient;
    }

    /**
     * @param MockClient $mockClient
     */
    protected function setMockClient(MockClient $mockClient)
    {
        $this->mockClient = $mockClient;
    }

    /**
     * @return \Closure
     */
    protected function getMockClientCallback()
    {
        return function () {
            return function ($httpConfig) {
                return $this->getGuzzleHttpClientMock();
            };
        };
    }

    /**
     * @return GuzzleHttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGuzzleHttpClientMock()
    {
        return $this->guzzleHttpClientMock;
    }
}
