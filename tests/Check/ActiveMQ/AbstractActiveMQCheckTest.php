<?php

namespace TonicHealthCheck\Tests\Check\ActiveMQ;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractActiveMQCheckTest.
 */
abstract class AbstractActiveMQCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $clientStomp;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $statefulStomp;

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getClientStomp()
    {
        return $this->clientStomp;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getStatefulStomp()
    {
        return $this->statefulStomp;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $clientStomp
     */
    protected function setClientStomp(PHPUnit_Framework_MockObject_MockObject $clientStomp)
    {
        $this->clientStomp = $clientStomp;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $statefulStomp
     */
    protected function setStatefulStomp($statefulStomp)
    {
        $this->statefulStomp = $statefulStomp;
    }
}
