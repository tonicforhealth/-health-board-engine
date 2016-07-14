<?php

namespace TonicHealthCheck\Check\Processing\MinAmountWorkers;

use Supervisor\Process;
use Supervisor\Supervisor;
use TonicHealthCheck\Check\Processing\AbstractProcessingCheck;

/**
 * Class ProcessingMinAmountWorkersCheck.
 */
class ProcessingMinAmountWorkersCheck extends AbstractProcessingCheck
{
    const CHECK = 'processing-min-amount-workers-check';

    const WORKERS_GROUP_MASK_REGEX = '/^worker-/';

    /**
     * @var Supervisor
     */
    protected $supervisor;

    /**
     * @var int
     */
    protected $minWorkersAmount;

    /**
     * @var string
     */
    protected $workersGroupMaskRegex;

    /**
     * ProcessingCheck constructor.
     *
     * @param string     $checkNode
     * @param Supervisor $supervisor
     * @param int        $minWorkersAmount
     * @param string     $workersGroupMaskRegex
     */
    public function __construct($checkNode, Supervisor $supervisor, $minWorkersAmount, $workersGroupMaskRegex = self::WORKERS_GROUP_MASK_REGEX)
    {
        parent::__construct($checkNode);
        $this->setSupervisor($supervisor);
        $this->setMinWorkersAmount($minWorkersAmount);
        $this->setWorkerGroupMaskRegex($workersGroupMaskRegex);
    }

    /**
     * @param int $minWorkersAmount
     *
     * @return bool|void
     *
     * @throws ProcessingMinAmountWorkersCheckException
     */
    public function check($minWorkersAmount = null)
    {
        $minWorkersAmount = null !== $minWorkersAmount ? $minWorkersAmount : $this->getMinWorkersAmount();

        $workersAmount = 0;

        try {
            $workers = $this->getSupervisor()->getAllProcesses();

            $workers = array_filter($workers, $this->getFilterWorkers());

            $workersAmount = count($workers);
        } catch (\Exception $e) {
            throw ProcessingMinAmountWorkersCheckException::internalProblem($e);
        }

        if ($minWorkersAmount > $workersAmount) {
            throw ProcessingMinAmountWorkersCheckException::workersMinAmount($minWorkersAmount, $workersAmount);
        }
    }

    /**
     * @return Supervisor
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @return int
     */
    public function getMinWorkersAmount()
    {
        return $this->minWorkersAmount;
    }

    /**
     * @return string
     */
    public function getWorkerGroupMaskRegex()
    {
        return $this->workersGroupMaskRegex;
    }

    /**
     * @param int $minWorkersAmount
     */
    protected function setMinWorkersAmount($minWorkersAmount)
    {
        $this->minWorkersAmount = $minWorkersAmount;
    }

    /**
     * @param string $workersGroupMaskRegex
     */
    protected function setWorkerGroupMaskRegex($workersGroupMaskRegex)
    {
        $this->workersGroupMaskRegex = $workersGroupMaskRegex;
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
}
