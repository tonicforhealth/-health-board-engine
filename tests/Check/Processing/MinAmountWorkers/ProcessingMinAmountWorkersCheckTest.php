<?php

namespace TonicHealthCheck\Tests\Check\Processing\StatusWorkers;

use Exception;
use Supervisor\Process;
use Supervisor\Supervisor;
use TonicHealthCheck\Check\Processing\MinAmountWorkers\ProcessingMinAmountWorkersCheck;
use TonicHealthCheck\Check\Processing\MinAmountWorkers\ProcessingMinAmountWorkersCheckException;
use TonicHealthCheck\Tests\Check\Processing\AbstractProcessingCheckTest;

/**
 * Class ProcessingMinAmountWorkersCheckTest.
 */
class ProcessingMinAmountWorkersCheckTest extends AbstractProcessingCheckTest
{
    const MIN_AMOUNT_WORKERS = 5;
    /**
     * @var ProcessingMinAmountWorkersCheck
     */
    private $processingMinAmountWorkersCheck;

    /**
     * Set up.
     */
    public function setUp()
    {
        $supervisor = $this
            ->getMockBuilder(Supervisor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setSupervisor($supervisor);

        $this->setProcessingMinAmountWorkersCheck(new ProcessingMinAmountWorkersCheck(
            'testnode',
            $this->getSupervisor(),
            static::MIN_AMOUNT_WORKERS
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->getSupervisor()
            ->method('getAllProcesses')
            ->willReturn(
                [
                    new Process(['group' => 'worker-1']),
                    new Process(['group' => 'worker-2']),
                    new Process(['group' => 'worker-3']),
                    new Process(['group' => 'worker-4']),
                    new Process(['group' => 'worker-5']),
                    new Process(['group' => 'worker-6']),
                ]
            );

        $checkResult = $this->getProcessingMinAmountWorkersCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is fail Exception.
     */
    public function testCheckIsFailException()
    {
        $exceptionMsg = 'RemoteCMD error';
        $exceptionCode = 1342;

        $this
            ->getSupervisor()
            ->method('getAllProcesses')
            ->willThrowException(
                new Exception(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $checkResult = $this->getProcessingMinAmountWorkersCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ProcessingMinAmountWorkersCheckException::CODE_INTERNAL_PROBLE, $checkResult->getError()->getCode());
        $this->assertRegExp('#'.$exceptionMsg.'#', $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            ProcessingMinAmountWorkersCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test is fail.
     */
    public function testCheckIsFail()
    {
        $this->getSupervisor()
            ->method('getAllProcesses')
            ->willReturn(
                [
                    new Process(['group' => 'worker-1']),
                    new Process(['group' => 'worker-2']),
                    new Process(['group' => 'worker-3']),
                    new Process(['group' => 'worker-4']),
                ]
            );

        $checkResult = $this->getProcessingMinAmountWorkersCheck()->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertEquals(ProcessingMinAmountWorkersCheckException::CODE_WORKER_MIN_AMOUNT, $checkResult->getError()->getCode());
        $this->assertInstanceOf(
            ProcessingMinAmountWorkersCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * @return ProcessingMinAmountWorkersCheck
     */
    public function getProcessingMinAmountWorkersCheck()
    {
        return $this->processingMinAmountWorkersCheck;
    }

    /**
     * @param ProcessingMinAmountWorkersCheck $processingMinAmountWorkersCheck
     */
    protected function setProcessingMinAmountWorkersCheck(ProcessingMinAmountWorkersCheck $processingMinAmountWorkersCheck)
    {
        $this->processingMinAmountWorkersCheck = $processingMinAmountWorkersCheck;
    }
}
