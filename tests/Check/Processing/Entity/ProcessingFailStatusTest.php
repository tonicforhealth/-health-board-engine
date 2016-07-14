<?php

namespace TonicHealthCheck\Tests\Check\Email\SendReceive\Entity;

use DateTime;
use TonicHealthCheck\Check\Processing\Entity\ProcessingFailStatus;

/**
 * Class ProcessingFailStatusTest.
 */
class ProcessingFailStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test doStuffOnPrePersist.
     */
    public function testDoStuffOnPrePersist()
    {
        $processingFailStatus = new ProcessingFailStatus();

        $id = 434234;

        $name = 'test_name_w344';

        $dateTime = new DateTime();

        $processingFailStatus->setId($id);

        $processingFailStatus->setName($name);

        $processingFailStatus->setFailAt($dateTime);

        $this->assertEquals($id, $processingFailStatus->getId());

        $this->assertEquals($name, $processingFailStatus->getName());

        $this->assertEquals($dateTime, $processingFailStatus->getFailAt());

        $this->assertEquals(null, $processingFailStatus->getLastFailStatus());
    }
}
