<?php

namespace TonicHealthCheck\Check\MountFS;

use TonicHealthCheck\Check\CheckException;

/**
 * Class MountFSCheckException.
 */
class MountFSCheckException extends CheckException
{
    const EXCEPTION_NAME = 'MountFSCheck';

    const CODE_INTERNAL_PROBLE = 6003;
    const TEXT_INTERNAL_PROBLE = 'docker API or server has internal problem:%s';

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
