<?php

namespace TonicHealthCheck\Tests\Integration\MountFS\Mount;

use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\MountFS\Mount\MountFSMountCheck;
use TonicHealthCheck\Check\MountFS\WriteReadDelete\MountFSWriteReadDeleteCheckException;
use TonicHealthCheck\RemoteCmd\RemoteCmd;
use TonicHealthCheck\Tests\Integration\MountFS\AbstractMountFSCheckTest;

/**
 * Class MountFSMountCheckTest.
 */
class MountFSMountCheckTest extends AbstractMountFSCheckTest
{
    /**
     * @var MountFSMountCheck
     */
    private $glusterFSMountCheck;

    /**
     * Set up.
     */
    public function setUp()
    {
        parent::setUp();
        $remoteCmd = $this
            ->getMockBuilder(RemoteCmd::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setRemoteCmd($remoteCmd);

        $this->setMountFSMountCheck(new MountFSMountCheck(
            'testnode',
            $this->getRemoteCmd()
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->getRemoteCmd()->method('getLastExitCode')->willReturn(0);

        $this->getChecksList()->add($this->getMountFSMountCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test glusterfs is down.
     */
    public function testGlusterfsIsDown()
    {
        $exceptionMsg = "MountFSMountCheck: MountFS mount point doesn\'t mount:\nThe SSH connection failed.";
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new MountFSWriteReadDeleteCheckException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSMountCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(6003, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return MountFSMountCheck
     */
    public function getMountFSMountCheck()
    {
        return $this->glusterFSMountCheck;
    }

    /**
     * @param MountFSMountCheck $glusterFSMountCheck
     */
    protected function setMountFSMountCheck(MountFSMountCheck $glusterFSMountCheck)
    {
        $this->glusterFSMountCheck = $glusterFSMountCheck;
    }
}
