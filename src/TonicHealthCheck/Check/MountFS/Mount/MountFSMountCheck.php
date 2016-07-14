<?php

namespace TonicHealthCheck\Check\MountFS\Mount;

use TonicHealthCheck\Check\MountFS\AbstractMountFSCheck;

/**
 * Class MountFSMountCheck.
 */
class MountFSMountCheck extends AbstractMountFSCheck
{
    const CHECK = 'mountfs-mount-check';

    const CHECK_MOUNT_GLUSTERFS_CMD = <<<CHECK_MOUNT_GLUSTERFS_CMD
    if [[ "$(grep %MOUNT_NAME% %CURRENT_MOUNT_LIST_FILE% | awk '{print $2}' | sort)" == "$(grep %MOUNT_NAME% %FSTAB_FILE% | awk '{print $2}' | sort)" ]] ; then
        G_EXIT_CODE=0
    else
        G_EXIT_CODE=1
        echo "%CURRENT_MOUNT_LIST_FILE%:" 1>&2
        cat  %CURRENT_MOUNT_LIST_FILE% 1>&2
        echo "\n\n%FSTAB_FILE%:" 1>&2
        cat %FSTAB_FILE% 1>&2
    fi

    (exit \$G_EXIT_CODE)
CHECK_MOUNT_GLUSTERFS_CMD;

    /**
     * @throws MountFSMountCheckException
     */
    public function check()
    {
        try {
            $resultStr = $this->getRemoteCmd()->exec($this->getCheckMountCmd());
        } catch (\Exception $e) {
            throw MountFSMountCheckException::internalProblem($e);
        }
        if ($this->getRemoteCmd()->getLastExitCode() != 0) {
            throw MountFSMountCheckException::doesNotMount($resultStr);
        }
    }

    /**
     * @return string
     */
    protected function getCheckMountCmd()
    {
        $checkMountCmd = static::CHECK_MOUNT_GLUSTERFS_CMD;

        $checkMountCmd = $this->replaceWithEnv($checkMountCmd);

        return $checkMountCmd;
    }
}
