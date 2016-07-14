<?php

namespace TonicHealthCheck\Check\ActiveMQ;

use TonicHealthCheck\Check\CheckException;

/**
 * Class ActiveMQCheckException.
 */
class ActiveMQCheckException extends CheckException
{
    const EXCEPTION_NAME = 'ActiveMQCheck';

    const CODE_INTERNAL_PROBLE = 4004;
    const TEXT_INTERNAL_PROBLE = 'ActiveMQ internal problem: %s';

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
