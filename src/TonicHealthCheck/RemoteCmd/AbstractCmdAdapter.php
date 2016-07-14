<?php

namespace TonicHealthCheck\RemoteCmd;

/**
 * Interface AbstractCmdAdapter.
 */
abstract class AbstractCmdAdapter implements CmdAdapterInterface
{
    /**
     * @var int
     */
    protected $lastExitCode;

    /**
     * @return mixed
     */
    public function getLastExitCode()
    {
        return $this->lastExitCode;
    }

    /**
     * @param mixed $lastExitCode
     */
    protected function setLastExitCode($lastExitCode)
    {
        $this->lastExitCode = $lastExitCode;
    }
}
