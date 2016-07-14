<?php

namespace TonicHealthCheck\RemoteCmd\CmdAdapter;

use Docker\Docker;
use TonicHealthCheck\RemoteCmd\AbstractCmdAdapter;
use TonicHealthCheck\RemoteCmd\CmdAdapterInterface;

/**
 * Class DockerCmdAdapter.
 */
class DockerCmdAdapter extends AbstractCmdAdapter implements CmdAdapterInterface
{
    protected $docker;
    protected $containerId;

    /**
     * DockerCmdAdapter constructor.
     *
     * @param Docker $docker
     * @param string $containerId
     */
    public function __construct(Docker $docker, $containerId)
    {
        $this->setDocker($docker);
        $this->setContainerId($containerId);
    }

    /**
     * @param string $cmd
     */
    public function exec($cmd)
    {
        $containerManager = $this->getDocker()->getContainerManager();
        $container = $containerManager->find($this->getContainerId());
        $execId = $containerManager->exec($container, ['/bin/bash', '-c', $cmd]);
        $result = $containerManager->execstart($execId);
        while ($statusObj = $containerManager->execinspect($execId)) {
            if (!$statusObj->Running) {
                break;
            }
            sleep(1);
        }
        $this->setLastExitCode($statusObj->ExitCode);

        return $result->getBody()->__toString();
    }

    /**
     * @return Docker
     */
    public function getDocker()
    {
        return $this->docker;
    }

    /**
     * @return mixed
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * @param mixed $containerId
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;
    }

    /**
     * @param Docker $docker
     */
    protected function setDocker($docker)
    {
        $this->docker = $docker;
    }
}
