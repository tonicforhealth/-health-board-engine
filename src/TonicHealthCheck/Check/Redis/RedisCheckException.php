<?php

namespace TonicHealthCheck\Check\Redis;

use TonicHealthCheck\Check\CheckException;

/**
 * Class RedisCheckException.
 */
class RedisCheckException extends CheckException
{
    const EXCEPTION_NAME = 'RedisCheck';

    const CODE_INTERNAL_PROBLE = 3004;
    const TEXT_INTERNAL_PROBLE = 'Redis internal problem: %s';

    /**
     * @param \Exception $e
     *
     * @return static
     */
    public static function internalProblem(\Exception $e)
    {
        return new static(sprintf(static::TEXT_INTERNAL_PROBLE, $e->getMessage()), static::CODE_INTERNAL_PROBLE, $e);
    }
}
