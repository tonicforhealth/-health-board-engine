<?php

namespace TonicHealthCheck\Tests\Integration\Processing;

use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class AbstractProcessingCheckTest.
 */
abstract class AbstractProcessingCheckTest extends AbstractIntegration
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
