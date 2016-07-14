<?php

namespace TonicHealthCheck\RemoteCmd\CmdAdapter;

use Ssh\Session;
use TonicHealthCheck\RemoteCmd\AbstractCmdAdapter;
use TonicHealthCheck\RemoteCmd\CmdAdapterInterface;

/**
 * Class SshCmdAdapter.
 */
class SshCmdAdapter extends AbstractCmdAdapter implements CmdAdapterInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * SshCmdAdapter constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->setSession($session);
    }

    /**
     * @param string $cmd
     *
     * @return mixed|string
     */
    public function exec($cmd)
    {
        $returnCode = 0;

        $exec = $this->getSession()->getExec();
        try {
            $return = $exec->run($cmd);
        } catch (\RuntimeException $e) {
            $returnCode = $e->getCode() == 0 ? 1 : $e->getCode();
            $return = $e->getMessage();
        }
        $this->setLastExitCode($returnCode);

        return $return;
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param Session $session
     */
    protected function setSession($session)
    {
        $this->session = $session;
    }
}
