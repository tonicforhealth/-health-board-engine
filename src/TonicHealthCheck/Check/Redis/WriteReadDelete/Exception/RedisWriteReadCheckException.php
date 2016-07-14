<?php

namespace TonicHealthCheck\Check\Redis\WriteReadDelete\Exception;

/**
 * Class RedisWriteReadCheckException.
 */
class RedisWriteReadCheckException extends RedisWRDCheckException
{
    const EXCEPTION_NAME = 'RedisWriteReadCheck';

    const CODE_DOES_NOT_SAVE = 3002;
    const TEXT_DOES_NOT_SAVE = 'Saved value beforeSave:%s set:%s current:%s';

    /**
     * @param string $beforeSaveValue
     * @param string $setValue
     * @param string $currentValue
     *
     * @return self
     */
    public static function doesNotSave($beforeSaveValue, $setValue, $currentValue)
    {
        return new self(sprintf(self::TEXT_DOES_NOT_SAVE, $beforeSaveValue, $setValue, $currentValue), self::CODE_DOES_NOT_SAVE);
    }
}
