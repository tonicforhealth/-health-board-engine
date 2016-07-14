<?php

namespace TonicHealthCheck\Tests\Check\Redis;

use Predis\Client as PredisClient;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractRedisCheckTest.
 */
abstract class AbstractRedisCheckTest extends PHPUnit_Framework_TestCase
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
