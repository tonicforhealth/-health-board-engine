<?php

namespace TonicHealthCheck\Tests\Integration\Processing\MinAmountWorkers;

use DateTime;
use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_MockObject_MockObject;
use Supervisor\Process;
use Supervisor\Supervisor;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\Processing\Entity\ProcessingFailStatus;
use TonicHealthCheck\Check\Processing\Entity\ProcessingFailStatusRepository;
use TonicHealthCheck\Check\Processing\StatusWorkers\ProcessingStatusWorkersCheck;
use TonicHealthCheck\Tests\Integration\Processing\AbstractProcessingCheckTest;

/**
 * Class ProcessingMinAmountWorkersCheckTest.
 */
class ProcessingStatusWorkersCheckTest extends AbstractProcessingCheckTest
{
    const WORKERS_GROUP_MASK_REGEX = '/^worker-/';
    /**
     * @var ProcessingStatusWorkersCheck
     */
    private $processingStatusWorkersCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject;
     */
    private $doctrine;

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

        $this->setDoctrine($this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock());

        $this->setProcessingStatusWorkersCheck(new ProcessingStatusWorkersCheck(
            'testnode',
            $this->getDoctrine(),
            $this->getSupervisor(),
            static::WORKERS_GROUP_MASK_REGEX
        ));
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $this->setUpEntity();

        $this->getSupervisor()
            ->method('getAllProcesses')
            ->willReturn(
                [
                    new Process(['group' => 'worker-1', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-2', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-3', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-5', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-6', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => 0]),
                ]
            );

        $this->getChecksList()->add($this->getProcessingStatusWorkersCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test processing is frozen.
     */
    public function testProcessingIsFrozen()
    {
        $this->setUpEntity();

        $this->getSupervisor()
            ->method('getAllProcesses')
            ->willReturn(
                [
                    new Process(['group' => 'worker-1', 'state' => 20, 'statename' => 'RUNNING', 'exitstatus' => -1]),
                    new Process(['group' => 'worker-2', 'state' => 30, 'statename' => 'BACKOFF', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-3', 'state' => 40, 'statename' => 'STOPPING', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-4', 'state' => 10, 'statename' => 'STARTING', 'exitstatus' => 293]),
                    new Process(['group' => 'worker-5', 'state' => 100, 'statename' => 'EXITED', 'exitstatus' => 0]),
                    new Process(['group' => 'worker-6', 'state' => 200, 'statename' => 'FATAL', 'exitstatus' => 24]),
                    new Process(['group' => 'worker-6', 'state' => 1000, 'statename' => 'UNKNOWN', 'exitstatus' => 0]),
                ]
            );

        $this->getChecksList()->add($this->getProcessingStatusWorkersCheck());

        $this->expectFireIncident(
            function (CheckException $exception) {
                $this->assertEquals(7002, $exception->getCode());
                $this->assertContains('Worker: status unhealthy:BACKOFF spawnerr: this worker has been unhealthy for', $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return ProcessingStatusWorkersCheck
     */
    public function getProcessingStatusWorkersCheck()
    {
        return $this->processingStatusWorkersCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param ProcessingStatusWorkersCheck $processingStatusWorkersCheck
     */
    protected function setProcessingStatusWorkersCheck(ProcessingStatusWorkersCheck $processingStatusWorkersCheck)
    {
        $this->processingStatusWorkersCheck = $processingStatusWorkersCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $doctrine
     */
    protected function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    private function setUpEntity()
    {
        $emailSendReceiveRepository = $this
            ->getMockBuilder(ProcessingFailStatusRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->getDoctrine()->method('getRepository')->willReturn($emailSendReceiveRepository);

        $emailSendReceive = new ProcessingFailStatus();

        $emailSendReceive->setFailAt(new DateTime('-1 day'));

        $emailSendReceiveRepository->method('findOneBy')->willReturn($emailSendReceive);
    }
}
