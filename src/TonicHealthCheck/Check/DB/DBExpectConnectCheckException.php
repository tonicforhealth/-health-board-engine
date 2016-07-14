<?php

namespace TonicHealthCheck\Check\DB;

/**
 * Class DBExpectConnectCheckException.
 */
class DBExpectConnectCheckException extends DBCheckException
{
    const EXCEPTION_NAME = 'DBExpectConnectCheck';

    const CODE_DISCONNECTED = 2001;
    const TEXT_DISCONNECTED = 'Fail perform any PDO method without pdo object instance, pls connect first.';

    /**
     * @return self
     */
    public static function expectConnected()
    {
        return new self(self::TEXT_DISCONNECTED, self::CODE_DISCONNECTED);
    }
}
