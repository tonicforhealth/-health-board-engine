<?php

namespace TonicHealthCheck\Tests\Check\Processing;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractProcessingCheckTest.
 */
abstract class AbstractProcessingCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $supervisor;

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $supervisor
     */
    protected function setSupervisor(PHPUnit_Framework_MockObject_MockObject $supervisor)
    {
        $this->supervisor = $supervisor;
    }
}
