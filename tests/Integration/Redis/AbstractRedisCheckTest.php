<?php

namespace TonicHealthCheck\Tests\Integration\Redis;

use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class AbstractRedisCheckTest.
 */
abstract class AbstractRedisCheckTest extends AbstractIntegration
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $predisClient;

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getPredisClient()
    {
        return $this->predisClient;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $predisClient
     */
    protected function setPredisClient(PHPUnit_Framework_MockObject_MockObject $predisClient)
    {
        $this->predisClient = $predisClient;
    }
}
