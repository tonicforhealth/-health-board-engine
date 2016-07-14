<?php

namespace TonicHealthCheck\Check\Elasticsearch;

use TonicHealthCheck\Check\CheckException;

/**
 * Class ElasticsearchCheckException.
 */
class ElasticsearchCheckException extends CheckException
{
    const EXCEPTION_NAME = 'ElasticsearchCheck';

    const CODE_INTERNAL_PROBLE = 5001;
    const TEXT_INTERNAL_PROBLE = 'Elasticsearch connect or GET problem: %s';

    /**
     * @param \Exception $e
     *
     * @return static
     */
    public static function internalGetProblem(\Exception $e)
    {
        return new static(sprintf(static::TEXT_INTERNAL_PROBLE, $e->getMessage()), static::CODE_INTERNAL_PROBLE, $e);
    }
}
