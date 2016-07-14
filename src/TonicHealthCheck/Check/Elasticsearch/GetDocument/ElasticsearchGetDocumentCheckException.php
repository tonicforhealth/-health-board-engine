<?php

namespace TonicHealthCheck\Check\Elasticsearch\GetDocument;

use TonicHealthCheck\Check\Elasticsearch\ElasticsearchCheckException;

/**
 * Class ElasticsearchGetDocumentCheckException.
 */
class ElasticsearchGetDocumentCheckException extends ElasticsearchCheckException
{
    const EXCEPTION_NAME = 'ElasticsearchGetDocumentCheck';

    const CODE_MIN_SIZE_INDEX = 5002;
    const TEXT_MIN_SIZE_INDEX = 'index:%s type:%s has %d element min count is %d';

    /**
     * @param string $index
     * @param string $type
     * @param int    $size
     * @param int    $minSize
     *
     * @return ElasticsearchGetDocumentCheckException
     */
    public static function emptyIndex($index, $type, $size, $minSize)
    {
        return new self(sprintf(self::TEXT_MIN_SIZE_INDEX, $index, $type, $size, $minSize), self::CODE_MIN_SIZE_INDEX);
    }
}
