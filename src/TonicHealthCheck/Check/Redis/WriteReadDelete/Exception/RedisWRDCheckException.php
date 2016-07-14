<?php

namespace TonicHealthCheck\Check\Redis\WriteReadDelete\Exception;

use TonicHealthCheck\Check\Redis\RedisCheckException;

/**
 * Class RedisWriteReadDeleteCheckException.
 */
class RedisWRDCheckException extends RedisCheckException
{
    const EXCEPTION_NAME = 'RedisWriteReadDeleteCheck';
}
