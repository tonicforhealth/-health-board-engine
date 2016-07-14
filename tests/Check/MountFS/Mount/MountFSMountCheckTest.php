<?php

namespace TonicHealthCheck\Tests\Check\MountFS\Mount;

use Exception;
use TonicHealthCheck\Check\MountFS\Mount\MountFSMountCheck;
use TonicHealthCheck\Check\MountFS\Mount\MountFSMountCheckException;
use TonicHealthCheck\RemoteCmd\RemoteCmd;
use TonicHealthCheck\Tests\Check\MountFS\AbstractMountFSCheckTest;

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

        $checkResult = $this->getMountFSMountCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is ok.
     */
    public function testCheckIsFailExitCode()
    {
        $this->getRemoteCmd()->method('getLastExitCode')->willReturn(234);

        $checkResult = $this->getMountFSMountCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(MountFSMountCheckException::CODE_DOES_NOT_MOUNT, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            MountFSMountCheckException::class,
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

        $checkResult = $this->getMountFSMountCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(MountFSMountCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            MountFSMountCheckException::class,
            $checkResult->getError()
        );
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
