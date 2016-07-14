<?php

namespace TonicHealthCheck\Check\Redis;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class RedisCheckCollection.
 */
class RedisCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractRedisCheck::class;
}
