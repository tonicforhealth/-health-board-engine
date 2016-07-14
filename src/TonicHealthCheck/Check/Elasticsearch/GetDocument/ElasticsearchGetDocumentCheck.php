<?php

namespace TonicHealthCheck\Check\Elasticsearch\GetDocument;

use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use TonicHealthCheck\Check\Elasticsearch\AbstractElasticsearchCheck;

/**
 * Class ElasticsearchGetDocumentCheck.
 */
class ElasticsearchGetDocumentCheck extends AbstractElasticsearchCheck
{
    const CHECK = 'elasticsearch-get-document-check';

    const INDEX_GET_SIZE = 5;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $index;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var int
     */
    protected $minSize;

    /**
     * @param string              $checkNode
     * @param ElasticsearchClient $client
     * @param string              $index
     * @param string              $type
     * @param int                 $minSize
     */
    public function __construct($checkNode, ElasticsearchClient $client, $index, $type, $minSize = self::INDEX_GET_SIZE)
    {
        parent::__construct($checkNode);
        $this->setClient($client);
        $this->setIndex($index);
        $this->setType($type);
        $this->setMinSize($minSize);
    }

    /**
     * Check elasticsearch client can get index type.
     *
     * @param string $index
     * @param string $type
     * @param int    $minSize
     *
     * @return bool|void
     *
     * @throws ElasticsearchGetDocumentCheckException
     */
    public function check($index = null, $type = null, $minSize = null)
    {
        if (null === $index) {
            $index = $this->getIndex();
        }

        if (null === $type) {
            $type = $this->getType();
        }

        if (null === $minSize) {
            $minSize = $this->getMinSize();
        }

        $params = [
            'size' => static::INDEX_GET_SIZE,
            'index' => $index,
            'type' => $type,

        ];
        try {
            $response = $this->getClient()->search($params);
        } catch (ElasticsearchException $e) {
            throw ElasticsearchGetDocumentCheckException::internalGetProblem($e);
        }

        $size = !empty($response['hits']['total']) ? $response['hits']['total'] : 0;

        if ($size < $minSize) {
            throw ElasticsearchGetDocumentCheckException::emptyIndex($index, $type, $size, $minSize);
        }
    }

    /**
     * @return ElasticsearchClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getMinSize()
    {
        return $this->minSize;
    }

    /**
     * @param string $index
     */
    protected function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * @param string $type
     */
    protected function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param int $minSize
     */
    protected function setMinSize($minSize)
    {
        $this->minSize = $minSize;
    }

    /**
     * @param ElasticsearchClient $client
     */
    protected function setClient(ElasticsearchClient $client)
    {
        $this->client = $client;
    }
}
