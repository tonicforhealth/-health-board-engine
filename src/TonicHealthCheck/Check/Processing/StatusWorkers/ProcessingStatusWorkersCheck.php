<?php

namespace TonicHealthCheck\Check\Processing\StatusWorkers;

use DateTime;
use Doctrine\ORM\EntityManager;
use Supervisor\Process;
use Supervisor\Supervisor;
use TonicHealthCheck\Check\Processing\AbstractProcessingCheck;
use TonicHealthCheck\Check\Processing\Entity\ProcessingFailStatus;

/**
 * Class ProcessingStatusWorkersCheck.
 */
class ProcessingStatusWorkersCheck extends AbstractProcessingCheck
{
    const CHECK = 'processing-status-workers-check';

    const WORKERS_GROUP_MASK_REGEX = '/^worker-/';

    const MAX_FAIL_TIME = 300;

    public static $workerValidStates = [
        'RUNNING' => 20,
    ];

    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var Supervisor
     */
    protected $supervisor;

    /**
     * @var string
     */
    protected $workersGroupMaskRegex;

    /**
     * @var int
     */
    private $maxFailTime;

    /**
     * ProcessingCheck constructor.
     *
     * @param string        $checkNode
     * @param EntityManager $doctrine
     * @param Supervisor    $supervisor
     * @param string        $workersGroupMaskRegex
     * @param int           $maxFailTime
     */
    public function __construct(
        $checkNode,
        EntityManager $doctrine,
        Supervisor $supervisor,
        $workersGroupMaskRegex = self::WORKERS_GROUP_MASK_REGEX,
        $maxFailTime = self::MAX_FAIL_TIME
    ) {
        parent::__construct($checkNode);
        $this->setDoctrine($doctrine);
        $this->setSupervisor($supervisor);
        $this->setWorkerGroupMaskRegex($workersGroupMaskRegex);
        $this->setMaxFailTime($maxFailTime);
    }

    /**
     * @throws ProcessingStatusWorkersCheckException
     */
    public function check()
    {
        try {
            $workers = $this->getSupervisor()->getAllProcesses();

            $workers = array_filter($workers, $this->getFilterWorkers());
        } catch (\Exception $e) {
            throw ProcessingStatusWorkersCheckException::internalProblem($e);
        }

        foreach ($workers as $worker) {
            $workerName = $worker['name'];
            $workerState = $worker['state'];
            $processingFailStatus = $this->getProcessingFailStatusByName($workerName);
            if (!in_array($workerState, static::$workerValidStates)) {
                if (null === $processingFailStatus) {
                    $processingFailStatus = $this->createProcessingFailStatus($workerName);
                }
                $processingFailStatus->setLastFailStatus($workerState);
                $this->persistProcessingFailStatus($processingFailStatus);
                $this->maxFailTimeCheck($worker, $processingFailStatus);
            } else {
                if (null !== $processingFailStatus) {
                    $this->removeProcessingFailStatus($processingFailStatus);
                }
            }
        }
    }

    /**
     * @return EntityManager
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return Supervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @return string
     */
    public function getWorkerGroupMaskRegex()
    {
        return $this->workersGroupMaskRegex;
    }

    /**
     * @param string $workersGroupMaskRegex
     */
    public function setWorkerGroupMaskRegex($workersGroupMaskRegex)
    {
        $this->workersGroupMaskRegex = $workersGroupMaskRegex;
    }

    /**
     * @return int
     */
    public function getMaxFailTime()
    {
        return $this->maxFailTime;
    }

    /**
     * @param null|string $workersGroupMaskRegex
     *
     * @return \Closure
     */
    protected function getFilterWorkers($workersGroupMaskRegex = null)
    {
        $workersGroupMaskRegex = null !== $workersGroupMaskRegex ? $workersGroupMaskRegex : $this->getWorkerGroupMaskRegex();

        return function (Process $worker) use ($workersGroupMaskRegex) {
            return preg_match($workersGroupMaskRegex, $worker['group']);
        };
    }

    /**
     * @param Supervisor $supervisor
     */
    protected function setSupervisor(Supervisor $supervisor)
    {
        $this->supervisor = $supervisor;
    }

    /**
     * @param EntityManager $doctrine
     */
    protected function setDoctrine(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param int $maxFailTime
     */
    protected function setMaxFailTime($maxFailTime)
    {
        $this->maxFailTime = $maxFailTime;
    }

    /**
     * @param $workerName
     *
     * @return null|ProcessingFailStatus
     */
    protected function getProcessingFailStatusByName($workerName)
    {
        $processingFailStatus = $this->getDoctrine()
            ->getRepository(ProcessingFailStatus::class)
            ->findOneBy(['name' => $workerName]);

        return $processingFailStatus;
    }

    /**
     * @param $workerName
     *
     * @return ProcessingFailStatus
     */
    protected function createProcessingFailStatus($workerName)
    {
        $processingFailStatus = new ProcessingFailStatus();
        $processingFailStatus->setFailAt(new DateTime());
        $processingFailStatus->setName($workerName);

        return $processingFailStatus;
    }

    /**
     * @param $processingFailStatus
     */
    protected function persistProcessingFailStatus($processingFailStatus)
    {
        $this->getDoctrine()->persist($processingFailStatus);
        $this->getDoctrine()->flush();
    }

    /**
     * @param $processingFailStatus
     */
    protected function removeProcessingFailStatus($processingFailStatus)
    {
        $this->getDoctrine()->remove($processingFailStatus);
        $this->getDoctrine()->flush();
    }

    /**
     * @param array                $worker
     * @param ProcessingFailStatus $processingFailStatus
     *
     * @throws ProcessingStatusWorkersCheckException
     */
    protected function maxFailTimeCheck($worker, ProcessingFailStatus $processingFailStatus)
    {
        $workerName = $worker['name'];
        $timeLeft = time() - $processingFailStatus->getFailAt()->getTimestamp();
        if ($timeLeft >= $this->getMaxFailTime()) {
            $workerStatusName = $worker['statename'];
            $workerSpawnErr = isset($worker['spawnerr']) ? $worker['spawnerr'] : '';
            throw ProcessingStatusWorkersCheckException::workerStatusUnhealthy(
                $workerName,
                $workerStatusName,
                $workerSpawnErr,
                $timeLeft
            );
        }
    }
}
