<?php

namespace TonicHealthCheck\tests\Integration\Elasticsearch\GetDocument;

use Elasticsearch\Client as ElasticsearchClient;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Elasticsearch\GetDocument\ElasticsearchGetDocumentCheck;
use TonicHealthCheck\Check\Elasticsearch\GetDocument\ElasticsearchGetDocumentCheckException;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class ElasticsearchGetDocumentCheckTest.
 */
class ElasticsearchGetDocumentCheckTest extends AbstractIntegration
{
    /**
     * @var ElasticsearchGetDocumentCheck
     */
    private $elasticsearchGetDocumentCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $elasticsearchClient;

    /**
     * set up.
     */
    public function setUp()
    {
        parent::setUp();
        $elasticsearchClient = $this
            ->getMockBuilder(ElasticsearchClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setElasticsearchClient($elasticsearchClient);

        $this->setElasticsearchGetDocumentCheck(new ElasticsearchGetDocumentCheck(
            'testnode',
            $this->getElasticsearchClient(),
            'default_data',
            'default_type',
            5
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this
            ->getElasticsearchClient()
            ->method('search')
            ->willReturn(
                [
                    'hits' => [
                        'total' => 1763,
                    ],
                ]
            );

        $this->getChecksList()->add($this->getElasticsearchGetDocumentCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test elasticsearch is down
     * Test elasticsearch is frozen (now the same).
     */
    public function testElasticsearchIsDown()
    {
        $exceptionMsg = 'No alive nodes found in your cluster';

        $this
            ->getElasticsearchClient()
            ->method('search')
            ->willThrowException(
                ElasticsearchGetDocumentCheckException::internalGetProblem(new Exception($exceptionMsg))
            );

        $this->getChecksList()->add($this->getElasticsearchGetDocumentCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(5001, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return ElasticsearchGetDocumentCheck
     */
    public function getElasticsearchGetDocumentCheck()
    {
        return $this->elasticsearchGetDocumentCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getElasticsearchClient()
    {
        return $this->elasticsearchClient;
    }

    /**
     * @param ElasticsearchGetDocumentCheck $elasticsearchGetDocumentCheck
     */
    protected function setElasticsearchGetDocumentCheck(ElasticsearchGetDocumentCheck $elasticsearchGetDocumentCheck)
    {
        $this->elasticsearchGetDocumentCheck = $elasticsearchGetDocumentCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $elasticsearchClient
     */
    protected function setElasticsearchClient($elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;
    }
}
