<?php

namespace TonicHealthCheck\Tests\Check\Elasticsearch\GetDocument;

use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TonicHealthCheck\Check\Elasticsearch\GetDocument\ElasticsearchGetDocumentCheck;
use TonicHealthCheck\Check\Elasticsearch\GetDocument\ElasticsearchGetDocumentCheckException;

/**
 * Class ElasticsearchGetDocumentCheckTest.
 */
class ElasticsearchGetDocumentCheckTest extends PHPUnit_Framework_TestCase
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

        parent::setUp();
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

        $checkResult = $this->getElasticsearchGetDocumentCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail.
     */
    public function testCheckIsFailMinTotalLess()
    {
        $this
            ->getElasticsearchClient()
            ->method('search')
            ->willReturn(
                [
                    'hits' => [
                        'total' => 2,
                    ],
                ]
            );

        $checkResult = $this->getElasticsearchGetDocumentCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertInstanceOf(
            ElasticsearchGetDocumentCheckException::class,
            $checkResult->getError()
        );
        $this->assertEquals(
            ElasticsearchGetDocumentCheckException::CODE_MIN_SIZE_INDEX,
            $checkResult->getError()->getCode()
        );
    }

    /**
     * Test is fail with exception.
     */
    public function testCheckClientException()
    {
        $exceptionMsg = 'Elasticsearch Client get index error: 404 code';
        $exceptionCode = 404;

        $this
            ->getElasticsearchClient()
            ->method('search')
            ->willThrowException(
                new Missing404Exception($exceptionMsg, $exceptionCode)
            );

        $checkResult = $this->getElasticsearchGetDocumentCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ElasticsearchGetDocumentCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ElasticsearchGetDocumentCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionCode 1999
     *
     * Test is fail with unexpected exception
     */
    public function testCheckClientUnexpectedException()
    {
        $exceptionMsg = 'Elasticsearch Client get index error: 404 code';
        $exceptionCode = 1999;

        $this
            ->getElasticsearchClient()
            ->method('search')
            ->willThrowException(
                new Exception($exceptionMsg, $exceptionCode)
            );

        $this->getElasticsearchGetDocumentCheck()->performCheck();
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
