<?php

namespace TonicHealthCheck\RemoteCmd;

/**
 * Interface CmdAdapterInterface.
 */
interface CmdAdapterInterface
{
    /**
     * @param string $cmd
     *
     * @return string string|false
     */
    public function exec($cmd);

    /**
     * @return int
     */
    public function getLastExitCode();
}
