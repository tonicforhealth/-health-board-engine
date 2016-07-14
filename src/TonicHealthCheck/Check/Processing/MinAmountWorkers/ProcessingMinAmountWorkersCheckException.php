<?php

namespace TonicHealthCheck\Check\Processing\MinAmountWorkers;

use TonicHealthCheck\Check\Processing\ProcessingCheckException;

/**
 * Class ProcessingMinAmountWorkersCheckException.
 */
class ProcessingMinAmountWorkersCheckException extends ProcessingCheckException
{
    const EXCEPTION_NAME = 'ProcessingMinAmountWorkersCheck';

    const CODE_WORKER_MIN_AMOUNT = 7001;
    const TEXT_WORKER_MIN_AMOUNT = 'Min amount of workers must be:%s current:%s';

    /**
     * @param int $minWorkersAmount
     * @param int $workersAmount
     *
     * @return self
     */
    public static function workersMinAmount($minWorkersAmount, $workersAmount)
    {
        return new self(sprintf(self::TEXT_WORKER_MIN_AMOUNT, $minWorkersAmount, $workersAmount), self::CODE_WORKER_MIN_AMOUNT);
    }
}
