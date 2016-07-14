<?php

namespace TonicHealthCheck\Check\Http\PerformanceDegradation;

use TonicHealthCheck\Check\Http\HttpCheckException;

/**
 * Class HttpPerformanceDegradationCheckException.
 */
class HttpPerformanceDegradationCheckException extends HttpCheckException
{
    const EXCEPTION_NAME = 'HttpPerformanceDegradationCheck';

    const CODE_PERFORMANCE_DEGRADATION = 1002;
    const TEXT_PERFORMANCE_DEGRADATION = 'Current HTTP responce average time is:%01.2fs. expected:%01.2fs.';

    /**
     * @param float $averageTime
     * @param float $averageMaxTime
     *
     * @return self
     */
    public static function performanceDegradation($averageTime, $averageMaxTime)
    {
        return new self(sprintf(self::TEXT_PERFORMANCE_DEGRADATION, $averageTime, $averageMaxTime), self::CODE_PERFORMANCE_DEGRADATION);
    }
}
