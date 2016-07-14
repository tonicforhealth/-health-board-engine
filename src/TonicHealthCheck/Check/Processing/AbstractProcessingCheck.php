<?php

namespace TonicHealthCheck\Check\Processing;

use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractProcessingCheck.
 */
abstract class AbstractProcessingCheck extends AbstractCheck
{
    const COMPONENT = 'processing';
    const GROUP = 'web';
}
