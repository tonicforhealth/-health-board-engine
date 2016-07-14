<?php

namespace TonicHealthCheck\Tests\Integration;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TonicHealthCheck\Checker\Checker;
use TonicHealthCheck\Checker\ChecksList;
use TonicHealthCheck\Component\ComponentManager;
use TonicHealthCheck\Entity\Component;
use TonicHealthCheck\Incident\IncidentManager;
use TonicHealthCheck\Maintenance\ScheduledMaintenance;
use Twig_Environment;

/**
 * Class AbstractInegration.
 */
abstract class AbstractIntegration extends PHPUnit_Framework_TestCase
{
    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputInterfaceMock;

    /**
     * @var IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputInterfaceMock;
    /**
     * @var ScheduledMaintenance|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduledMaintenanceMock;

    /**
     * @var ComponentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $componentManagerMock;

    /**
     * @var IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $incidentManagerMock;

    /**
     * @var ChecksList
     */
    private $checksList;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Twig_Environment
     */
    private $twigEnvironmentMock;

    /**
     * @var Checker
     */
    private $checker;

    /**
     * Set up for AbstractHttpCheckTest.
     */
    public function setUp()
    {
        parent::setUp();

        $this->checksList = $this->createChecksList();
        $this->scheduledMaintenanceMock = $this->createScheduledMaintenanceMock();
        $this->componentManagerMock = $this->createComponentManagerMock();
        $this->incidentManagerMock = $this->createIncidentManagerMock();
        $this->twigEnvironmentMock = $this->createTwigEnvironmentMock();

        $this->inputInterfaceMock = $this->createInputInterfaceMock();
        $this->outputInterfaceMock = $this->createOutputInterfaceMock();

        $this->checker = $this->createChecker();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Twig_Environment
     */
    protected function getTwigEnvironmentMock()
    {
        return $this->twigEnvironmentMock;
    }

    /**
     * @return ChecksList
     */
    protected function getChecksList()
    {
        return $this->checksList;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ScheduledMaintenance
     */
    protected function getScheduledMaintenanceMock()
    {
        return $this->scheduledMaintenanceMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ComponentManager
     */
    protected function getComponentManagerMock()
    {
        return $this->componentManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IncidentManager
     */
    protected function getIncidentManagerMock()
    {
        return $this->incidentManagerMock;
    }

    /**
     * @return Checker
     */
    protected function getChecker()
    {
        return $this->checker;
    }

    /**
     * @return InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInputInterfaceMock()
    {
        return $this->inputInterfaceMock;
    }

    /**
     * @return OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOutputInterfaceMock()
    {
        return $this->outputInterfaceMock;
    }

    /**
     * @return IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createIncidentManagerMock()
    {
        return $this->getMockBuilder(IncidentManager::class)->disableOriginalConstructor(
        )->getMock();
    }

    /**
     * @return ComponentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createComponentManagerMock()
    {
        $componentManagerMock = $this
            ->getMockBuilder(ComponentManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $component = $this->getMockBuilder(Component::class)->getMock();

        $componentManagerMock
            ->method('getComponentByName')
            ->willReturn($component);

        return $componentManagerMock;
    }

    /**
     * @return ScheduledMaintenance|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createScheduledMaintenanceMock()
    {
        $sMaintenanceMock = $this
            ->getMockBuilder(ScheduledMaintenance::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sMaintenanceMock->method('isNowMaintenanceOn')->willReturn(false);

        return $sMaintenanceMock;
    }
    /**
     * @return InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createInputInterfaceMock()
    {
        return $this->getMockBuilder(InputInterface::class)->getMock();
    }

    /**
     * @return OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createOutputInterfaceMock()
    {
        return $this->getMockBuilder(OutputInterface::class)->getMock();
    }

    /**
     * @return Checker|\PHPUnit_Framework_MockObject_MockObject|OutputInterface
     */
    protected function createChecker()
    {
        return new Checker(
            $this->getChecksList(),
            $this->getComponentManagerMock(),
            $this->getIncidentManagerMock(),
            $this->getScheduledMaintenanceMock(),
            $this->getTwigEnvironmentMock()
        );
    }

    protected function performChecks()
    {
        $this->getChecker()->performChecks(
            $this->getInputInterfaceMock(),
            $this->getOutputInterfaceMock()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Twig_Environment
     */
    protected function createTwigEnvironmentMock()
    {
        return $this->getMockBuilder(Twig_Environment::class)->getMock();
    }

    /**
     * @return ChecksList
     */
    protected function createChecksList()
    {
        return new ChecksList();
    }

    /**
     * @param $assertExceptionCallback
     */
    protected function expectFireIncident($assertExceptionCallback)
    {
        $this->getIncidentManagerMock()
            ->expects($this->once())
            ->method('fireIncident')
        ->with(
            $this->equalTo($this->getChecksList()->at(0)),
            $this->callback(
                $assertExceptionCallback
            )
        );
    }

    protected function expectResolveIncident()
    {
        $this->getIncidentManagerMock()
            ->expects($this->once())
            ->method('resolveIncident')
        ->with(
            $this->equalTo($this->getChecksList()->at(0)));
    }
}
