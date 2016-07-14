<?php

namespace TonicHealthCheck\Check\Processing\StatusWorkers;

use TonicHealthCheck\Check\Processing\ProcessingCheckException;

/**
 * Class ProcessingStatusWorkersCheckException.
 */
class ProcessingStatusWorkersCheckException extends ProcessingCheckException
{
    const EXCEPTION_NAME = 'ProcessingStatusWorkersCheck';

    const CODE_WORKER_STATUS_UNHEALTHY = 7002;
    const TEXT_WORKER_STATUS_UNHEALTHY = 'Worker:%s status unhealthy:%s spawnerr:%s this worker has been unhealthy for %dsec.';

    /**
     * @param string $worker
     * @param string $status
     * @param string $spawnerr
     * @param int    $timeLeft
     *
     * @return ProcessingStatusWorkersCheckException
     */
    public static function workerStatusUnhealthy($worker, $status, $spawnerr, $timeLeft)
    {
        return new self(sprintf(self::TEXT_WORKER_STATUS_UNHEALTHY, $worker, $status, $spawnerr, $timeLeft), self::CODE_WORKER_STATUS_UNHEALTHY);
    }
}
