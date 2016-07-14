<?php

namespace TonicHealthCheck\Check\Processing;

use TonicHealthCheck\Check\CheckException;

/**
 * Class ProcessingCheckException.
 */
class ProcessingCheckException extends CheckException
{
    const EXCEPTION_NAME = 'ProcessingCheck';

    const CODE_INTERNAL_PROBLE = 7003;
    const TEXT_INTERNAL_PROBLE = 'Proccesing server has internal problem:%s';

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
