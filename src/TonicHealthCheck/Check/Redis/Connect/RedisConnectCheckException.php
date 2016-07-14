<?php

namespace TonicHealthCheck\Check\Redis\Connect;

use TonicHealthCheck\Check\Redis\RedisCheckException;

/**
 * Class RedisConnectCheckException.
 */
class RedisConnectCheckException extends RedisCheckException
{
    const EXCEPTION_NAME = 'RedisConnectCheck';

    const CODE_CONNECT_PROBLE = 3001;
    const TEXT_CONNECT_PROBLE = 'Redis connect problem: %s';

    /**
     * @param \Exception $e
     *
     * @return self
     */
    public static function connectProblem(\Exception $e)
    {
        return new self(sprintf(self::TEXT_CONNECT_PROBLE, $e->getMessage()), self::CODE_CONNECT_PROBLE, $e);
    }
}
