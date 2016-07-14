<?php

namespace TonicHealthCheck\Check\Http\Code;

use TonicHealthCheck\Check\Http\HttpCheckException;

/**
 * Class HttpCodeCheckException.
 */
class HttpCodeCheckException extends HttpCheckException
{
    const EXCEPTION_NAME = 'HttpCheckCode';

    const CODE_UNEXPECTED_HTTP_CODE = 1001;
    const TEXT_UNEXPECTED_HTTP_CODE = 'GET request return CODE:%d expected CODE:%d';

    /**
     * @param int $currentCode
     * @param int $expectedCode
     *
     * @return self
     */
    public static function unexpectedHttpCode($currentCode, $expectedCode)
    {
        return new self(sprintf(self::TEXT_UNEXPECTED_HTTP_CODE, $currentCode, $expectedCode), self::CODE_UNEXPECTED_HTTP_CODE);
    }
}
