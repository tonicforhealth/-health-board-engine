<?php

namespace TonicHealthCheck\Check\Redis\WriteReadDelete\Exception;

/**
 * Class RedisWriteReadDeleteCheckException.
 */
class RedisDeleteCheckException extends RedisWRDCheckException
{
    const EXCEPTION_NAME = 'RedisDeleteCheck';

    const CODE_DOES_NOT_DELETE = 3003;
    const TEXT_DOES_NOT_DELETE = 'Delete value beforeDelete:%s stay:%s';

    /**
     * @param string $beforeDeleteValue
     * @param string $currentValue
     *
     * @return self
     */
    public static function doesNotDelete($beforeDeleteValue, $currentValue)
    {
        return new self(sprintf(self::TEXT_DOES_NOT_DELETE, $beforeDeleteValue, $currentValue), self::CODE_DOES_NOT_DELETE);
    }
}
