<?php

namespace TonicHealthCheck\Tests\Integration\MountFS\WriteReadDelete;

use Exception;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\MountFS\WriteReadDelete\MountFSWriteReadDeleteCheck;
use TonicHealthCheck\RemoteCmd\RemoteCmd;
use TonicHealthCheck\Tests\Integration\MountFS\AbstractMountFSCheckTest;

/**
 * Class MountFSWriteReadDeleteCheckTest.
 */
class MountFSWriteReadDeleteCheckTest extends AbstractMountFSCheckTest
{
    /**
     * @var MountFSWriteReadDeleteCheck
     */
    private $glusterFSWriteReadDeleteCheck;

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

        $this->setMountFSWriteReadDeleteCheck(new MountFSWriteReadDeleteCheck(
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

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test glusterfs is down (stop an FS server).
     */
    public function testGlusterfsIsDown()
    {
        $exceptionMsg = 'bash: line 3: /mountdir/testHealthCheckFile: Operation timed out';
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

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
     * Test glusterfs is down (unmount a dir, then delete it).
     */
    public function testGlusterfsIsUnmounted()
    {
        $exceptionMsg = 'bash: line 3: /mountdir/testHealthCheckFile: No such file or directory';
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

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
     * Test glusterfs can't create or write to files in mounted dirs.
     */
    public function testGlusterfsCanNotCreateFile()
    {
        $exceptionMsg = 'bash: line 3: echo: write error: No space left on device';
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

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
     * Test can't create&write&delete new files in the mounted dirs
     * change permission for the mount point to the readonly mode.
     */
    public function testGlusterfsRWDproblem()
    {
        $exceptionMsg = 'bash: line 3: /mountdir/testHealthCheckFile: No such file or directory';
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

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
     * Test can't create&write&delete new files in the mounted dirs
     * change permission for the mount point to the readonly mode.
     */
    public function testGlusterfsPermissionProblem()
    {
        $exceptionMsg = 'bash: line 3: echo: write error: Permission denied';
        $exceptionCode = 0;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getMountFSWriteReadDeleteCheck());

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
     * @return MountFSWriteReadDeleteCheck
     */
    public function getMountFSWriteReadDeleteCheck()
    {
        return $this->glusterFSWriteReadDeleteCheck;
    }

    /**
     * @param MountFSWriteReadDeleteCheck $glusterFSWriteReadDeleteCheck
     */
    protected function setMountFSWriteReadDeleteCheck(MountFSWriteReadDeleteCheck $glusterFSWriteReadDeleteCheck)
    {
        $this->glusterFSWriteReadDeleteCheck = $glusterFSWriteReadDeleteCheck;
    }
}
