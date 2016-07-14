<?php

namespace TonicHealthCheck\Check\Redis;

use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractRedisCheck.
 */
abstract class AbstractRedisCheck extends AbstractCheck
{
    const COMPONENT = 'redis';
    const GROUP = 'web';
}
