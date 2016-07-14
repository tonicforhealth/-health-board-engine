<?php

namespace TonicHealthCheck\Check\MountFS\WriteReadDelete;

use TonicHealthCheck\Check\MountFS\MountFSCheckException;

/**
 * Class MountFSMountCheckException.
 */
class MountFSWriteReadDeleteCheckException extends MountFSCheckException
{
    const EXCEPTION_NAME = 'MountFSWriteReadDeleteCheck';

    const CODE_WRD_FAIL_MOUNT = 6002;
    const TEXT_WRD_FAIL_MOUNT = "MountFS write|read|delete check fail:\n%s";

    /**
     * @param string $errorText
     *
     * @return self
     */
    public static function wrdFail($errorText)
    {
        return new self(sprintf(self::TEXT_WRD_FAIL_MOUNT, $errorText), self::CODE_WRD_FAIL_MOUNT);
    }
}
