<?php

namespace TonicHealthCheck\Check\ActiveMQ\Connect;

use TonicHealthCheck\Check\ActiveMQ\ActiveMQCheckException;

/**
 * Class ActiveMQConnectCheckException.
 */
class ActiveMQConnectCheckException extends ActiveMQCheckException
{
    const EXCEPTION_NAME = 'ActiveMQConnectCheck';

    const CODE_CONNECT_PROBLE = 4001;
    const TEXT_CONNECT_PROBLE = 'ActiveMQ connect problem: %s';

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
