<?php

namespace TonicHealthCheck\Check\MountFS\WriteReadDelete;

use TonicHealthCheck\Check\MountFS\AbstractMountFSCheck;

/**
 * Class MountFSWriteReadDeleteCheck.
 */
class MountFSWriteReadDeleteCheck extends AbstractMountFSCheck
{
    const CHECK = 'mountfs-write-read-delete-check';

    const CHECK_WRD_GLUSTERFS_CMD = <<<CHECK_WRD_GLUSTERFS_CMD
    G_EXIT_CODE=0
    while read -r path
    do
       echo "test1" > \$path/testHealthCheckFile && cat \$path/testHealthCheckFile | grep test1 && rm \$path/testHealthCheckFile
       LAST_EXIT_CODE=\$?
       [[ \$LAST_EXIT_CODE != 0 ]] && G_EXIT_CODE=\$LAST_EXIT_CODE
    done < <( grep %MOUNT_NAME% %CURRENT_MOUNT_LIST_FILE% | awk '{print $2}' )

    (exit \$G_EXIT_CODE)
CHECK_WRD_GLUSTERFS_CMD;

    /**
     * @throws MountFSWriteReadDeleteCheckException
     */
    public function check()
    {
        try {
            $resultStr = $this->getRemoteCmd()->exec($this->getCheckMountCmd());
        } catch (\Exception $e) {
            throw MountFSWriteReadDeleteCheckException::internalProblem($e);
        }
        if ($this->getRemoteCmd()->getLastExitCode() != 0) {
            throw MountFSWriteReadDeleteCheckException::wrdFail($resultStr);
        }
    }

    /**
     * @return string
     */
    protected function getCheckMountCmd()
    {
        $checkMountCmd = static::CHECK_WRD_GLUSTERFS_CMD;

        $checkMountCmd = $this->replaceWithEnv($checkMountCmd);

        return $checkMountCmd;
    }
}
