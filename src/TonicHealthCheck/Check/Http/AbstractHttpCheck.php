<?php

namespace TonicHealthCheck\Check\Http;

use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractHttpCheck.
 */
abstract class AbstractHttpCheck extends AbstractCheck
{
    const COMPONENT = 'http';
    const GROUP = 'web';
}
