<?php

namespace TonicHealthCheck\Tests\Check\MountFS;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractMountFSCheckTest.
 */
abstract class AbstractMountFSCheckTest extends PHPUnit_Framework_TestCase
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
