<?php

namespace TonicHealthCheck\Tests\Integration\Processing\StatusWorkers;

use Ivory\HttpAdapter\HttpAdapterException;
use Supervisor\Process;
use Supervisor\Supervisor;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Processing\MinAmountWorkers\ProcessingMinAmountWorkersCheck;
use TonicHealthCheck\Tests\Integration\Processing\AbstractProcessingCheckTest;

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
        parent::setUp();
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

        $this->getChecksList()->add($this->getProcessingMinAmountWorkersCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test processing is down.
     */
    public function testProcessingIsDown()
    {
        $exceptionMsg = 'An error occurred when fetching the URI "http://localhost:9001/RPC2" with the adapter "guzzle5" ("cURL error 7: Failed to connect to localhost port 9001: Connection refused").';
        $exceptionCode = 0;

        $this
            ->getSupervisor()
            ->method('getAllProcesses')
            ->willThrowException(
                new HttpAdapterException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $this->getChecksList()->add($this->getProcessingMinAmountWorkersCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(7003, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
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
