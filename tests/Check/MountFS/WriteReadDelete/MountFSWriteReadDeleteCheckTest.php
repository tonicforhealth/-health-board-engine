<?php

namespace TonicHealthCheck\Tests\Check\MountFS\WriteReadDelete;

use Exception;
use TonicHealthCheck\Check\MountFS\WriteReadDelete\MountFSWriteReadDeleteCheck;
use TonicHealthCheck\Check\MountFS\WriteReadDelete\MountFSWriteReadDeleteCheckException;
use TonicHealthCheck\RemoteCmd\RemoteCmd;
use TonicHealthCheck\Tests\Check\MountFS\AbstractMountFSCheckTest;

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

        $checkResult = $this->getMountFSWriteReadDeleteCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is ok.
     */
    public function testCheckIsFailExitCode()
    {
        $this->getRemoteCmd()->method('getLastExitCode')->willReturn(234);

        $checkResult = $this->getMountFSWriteReadDeleteCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(MountFSWriteReadDeleteCheckException::CODE_WRD_FAIL_MOUNT, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            MountFSWriteReadDeleteCheckException::class,
            $checkResult->getError()
        );
    }
    /**
     * Test is fail Exception.
     */
    public function testCheckIsFailException()
    {
        $exceptionMsg = 'MountFS Server error';
        $exceptionCode = 1395;

        $this
            ->getRemoteCmd()
            ->method('exec')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getMountFSWriteReadDeleteCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(MountFSWriteReadDeleteCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            MountFSWriteReadDeleteCheckException::class,
            $checkResult->getError()
        );
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
