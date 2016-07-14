<?php

namespace TonicHealthCheck\Tests\Integration\MountFS;

use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class AbstractMountFSCheckTest.
 */
abstract class AbstractMountFSCheckTest extends AbstractIntegration
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $remoteCmd;

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getRemoteCmd()
    {
        return $this->remoteCmd;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $remoteCmd
     */
    protected function setRemoteCmd(PHPUnit_Framework_MockObject_MockObject $remoteCmd)
    {
        $this->remoteCmd = $remoteCmd;
    }
}
