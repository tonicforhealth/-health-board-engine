<?php

namespace TonicHealthCheck\Check\MountFS;

use TonicHealthCheck\Check\AbstractCheck;
use TonicHealthCheck\RemoteCmd\RemoteCmd;

/**
 * Class AbstractMountFSCheck.
 */
abstract class AbstractMountFSCheck extends AbstractCheck
{
    const COMPONENT = 'mountfs';
    const GROUP = 'web';

    const MOUNT_NAME = 'nfs';
    const FSTAB_FILE = '/etc/fstab';
    const CURRENT_MOUNT_LIST_FILE = '/proc/mounts';

    /**
     * @var RemoteCmd
     */
    protected $remoteCmd;

    /**
     * @var string
     */
    protected $fstabFile = self::FSTAB_FILE;

    /**
     * @var string
     */
    protected $mountName = self::MOUNT_NAME;

    /**
     * @var string
     */
    protected $currentMountListFile = self::CURRENT_MOUNT_LIST_FILE;

    /**
     * @param string      $checkNode
     * @param RemoteCmd   $remoteCmd
     * @param string|null $mountName
     * @param string|null $fstabFile
     * @param string|null $currentMountListFile
     */
    public function __construct(
        $checkNode,
        RemoteCmd $remoteCmd,
        $mountName = null,
        $fstabFile = null,
        $currentMountListFile = null
    ) {
        parent::__construct($checkNode);

        if (null !== $mountName) {
            $this->setMountName($mountName);
        }

        if (null !== $fstabFile) {
            $this->setFstabFile($fstabFile);
        }

        if (null !== $currentMountListFile) {
            $this->setCurrentMountListFile($currentMountListFile);
        }

        $this->setRemoteCmd($remoteCmd);
    }

    /**
     * @return RemoteCmd
     */
    public function getRemoteCmd()
    {
        return $this->remoteCmd;
    }

    /**
     * @return string
     */
    public function getCurrentMountListFile()
    {
        return $this->currentMountListFile;
    }

    /**
     * @return string
     */
    public function getMountName()
    {
        return $this->mountName;
    }

    /**
     * @return string
     */
    public function getFstabFile()
    {
        return $this->fstabFile;
    }

    /**
     * @param RemoteCmd $remoteCmd
     */
    protected function setRemoteCmd($remoteCmd)
    {
        $this->remoteCmd = $remoteCmd;
    }

    /**
     * @param string $mountName
     */
    protected function setMountName($mountName)
    {
        $this->mountName = $mountName;
    }

    /**
     * @param string $fstabFile
     */
    protected function setFstabFile($fstabFile)
    {
        $this->fstabFile = $fstabFile;
    }

    /**
     * @param string $currentMountListFile
     */
    protected function setCurrentMountListFile($currentMountListFile)
    {
        $this->currentMountListFile = $currentMountListFile;
    }

    /**
     * @param string $checkMountCmd
     *
     * @return string
     */
    protected function replaceWithEnv($checkMountCmd)
    {
        $checkMountCmd = str_replace('%MOUNT_NAME%', $this->getMountName(), $checkMountCmd);
        $checkMountCmd = str_replace('%FSTAB_FILE%', $this->getFstabFile(), $checkMountCmd);
        $checkMountCmd = str_replace('%CURRENT_MOUNT_LIST_FILE%', $this->getCurrentMountListFile(), $checkMountCmd);

        return $checkMountCmd;
    }
}
