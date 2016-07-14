<?php

namespace TonicHealthCheck\RemoteCmd;

/**
 * Proxy Class RemoteCmd.
 */
class RemoteCmd
{
    /**
     * @var CmdAdapterInterface
     */
    protected $cmdAdapter;

    /**
     * RemoteCmd constructor.
     *
     * @param CmdAdapterInterface $cmdAdapter
     */
    public function __construct(CmdAdapterInterface $cmdAdapter)
    {
        $this->setCmdAdapter($cmdAdapter);
    }

    /**
     * @param string $cmd
     *
     * @return string
     */
    public function exec($cmd)
    {
        return $this->getCmdAdapter()->exec($cmd);
    }

    /**
     * @return int
     */
    public function getLastExitCode()
    {
        return $this->getCmdAdapter()->getLastExitCode();
    }

    /**
     * @return CmdAdapterInterface
     */
    protected function getCmdAdapter()
    {
        return $this->cmdAdapter;
    }

    /**
     * @param CmdAdapterInterface $cmdAdapter
     */
    protected function setCmdAdapter(CmdAdapterInterface $cmdAdapter)
    {
        $this->cmdAdapter = $cmdAdapter;
    }
}
