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
     * Test 3 time fails the resolve.
     */
    public function testCheckIsFailThenResolve()
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

        $this->failTest();

        $this->resolveFailedTest();
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

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected function getExpectedTableIncidentStatFirst()
    {
        return $this->createArrayDataSet(
            [
                'incident_stat' => [
                    [
                        'incident_id' => 1,
                        'ident' => 'local.web.http.http-code-check',
                        'type' => 'minor',
                        'status' => -1,
                        'resolved' => 0,
                    ],
                    [
                        'incident_id' => 2,
                        'ident' => 'local.web.http.http-performance-degradation-check',
                        'type' => 'urgent',
                        'status' => -1,
                        'resolved' => 0,
                    ],
                ],
            ]
        )->getTable('incident_stat');
    }

    /**
     * @return \PHPUnit_Extensions_Database_DataSet_ITable
     */
    protected function getExpectedTableIncidentStatThird()
    {
        return $this->createArrayDataSet(
            [
                'incident_stat' => [
                    [
                        'incident_id' => 1,
                        'ident' => 'local.web.http.http-code-check',
                        'type' => 'minor',
                        'status' => -1,
                        'resolved' => 0,
                    ],
                    [
                        'incident_id' => 1,
                        'ident' => 'local.web.http.http-code-check',
                        'type' => 'minor',
                        'status' => -1,
                        'resolved' => 0,
                    ],                    [
                        'incident_id' => 1,
                        'ident' => 'local.web.http.http-code-check',
                        'type' => 'urgent',
                        'status' => -1,
                        'resolved' => 0,
                    ],
                ],
            ]
        )->getTable('incident_stat');
    }

    protected function resolveFailedTest()
    {
        $this->getIncidentManagerMock()->resolveIncident(
            $this->findCheck('local.web.http.http-code-check')
        );

        $actualTable = $this->getConnection()->createQueryTable(
            'incident_stat',
            'SELECT *  FROM `incident_stat`'.
            ' WHERE `ident` = "local.web.http.http-code-check" and `resolved` = 1'
        );

        $this->assertEquals(1, $actualTable->getRowCount());

        $actualTable = $this->getConnection()->createQueryTable(
            'incident',
            'SELECT *  FROM `incident`'.
            ' WHERE `ident` = "local.web.http.http-code-check" and `resolved` = 1'
        );

        $this->assertEquals(1, $actualTable->getRowCount());
    }

    protected function failTest()
    {
        $this->performChecks();

        $this->assertEquals(2, $this->getConnection()->getRowCount('incident_stat'));
        $this->assertEquals(2, $this->getConnection()->getRowCount('incident'));

        $actualTable = $this->getConnection()->createQueryTable(
            'incident_stat',
            'SELECT `incident_id`, `ident`, `type`, `status`, `resolved`  FROM `incident_stat`'
        );

        $this->assertTablesEqual($this->getExpectedTableIncidentStatFirst(), $actualTable);

        $this->performChecks();
        $this->performChecks();

        $this->assertEquals(6, $this->getConnection()->getRowCount('incident_stat'));
        $this->assertEquals(2, $this->getConnection()->getRowCount('incident'));

        $actualTable = $this->getConnection()->createQueryTable(
            'incident_stat',
            'SELECT `incident_id`, `ident`, `type`, `status`, `resolved`  FROM `incident_stat`'.
            ' WHERE `ident` = "local.web.http.http-code-check"'
        );

        $this->assertTablesEqual($this->getExpectedTableIncidentStatThird(), $actualTable);
    }
}
