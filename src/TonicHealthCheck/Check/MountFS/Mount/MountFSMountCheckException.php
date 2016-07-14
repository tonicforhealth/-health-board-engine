<?php

namespace TonicHealthCheck\Check\MountFS\Mount;

use TonicHealthCheck\Check\MountFS\MountFSCheckException;

/**
 * Class MountFSMountCheckException.
 */
class MountFSMountCheckException extends MountFSCheckException
{
    const EXCEPTION_NAME = 'MountFSMountCheck';

    const CODE_DOES_NOT_MOUNT = 6001;
    const TEXT_DOES_NOT_MOUNT = "MountFS mount point doesn't mount:\n%s";

    /**
     * @param string $points
     *
     * @return self
     */
    public static function doesNotMount($points)
    {
        return new self(sprintf(self::TEXT_DOES_NOT_MOUNT, $points), self::CODE_DOES_NOT_MOUNT);
    }
}
