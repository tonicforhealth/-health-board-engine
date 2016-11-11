<?php

namespace TonicHealthCheck\tests\Integration\System;

use Doctrine\ORM\EntityManager;
use Http\Client\Common\HttpMethodsClient;
use Http\Discovery\MessageFactoryDiscovery;
use PHPUnit_Framework_TestCase;
use Pimple\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TonicHealthCheck\Bootstrap\Kernel;
use TonicHealthCheck\Check\CheckInterface;
use TonicHealthCheck\Checker\Checker;
use TonicHealthCheck\Checker\ChecksList;
use TonicHealthCheck\Component\ComponentManager;
use TonicHealthCheck\Entity\Component;
use TonicHealthCheck\Entity\ComponentRepository;
use TonicHealthCheck\Entity\Incident;
use TonicHealthCheck\Entity\IncidentRepository;
use TonicHealthCheck\Incident\IncidentManager;
use TonicHealthCheck\Maintenance\ScheduledMaintenance;
use Http\Mock\Client as MockClient;

/**
 * Class AbstractInegration.
 */
abstract class AbstractSystemTest extends PHPUnit_Framework_TestCase
{
    const TEST_ENV = 'test';

    /**
     * @var ScheduledMaintenance|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sMaintenanceMock;

    /**
     * @var ComponentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentManagerMock;

    /**
     * @var IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $incidentManagerMock;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputInterface;

    /**
     * @var IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputInterface;

    /**
     * Set up for AbstractHttpCheckTest.
     */
    public function setUp()
    {
        parent::setUp();
        $this->getKernel()->boot();

        $this->inputInterface = $this->getMockBuilder(InputInterface::class)->getMock();
        $this->outputInterface = $this->getMockBuilder(OutputInterface::class)->getMock();

        $this->sMaintenanceMock = $this->createScheduledMaintenanceMock();

        $this->componentManagerMock = $this->createComponentManagerMock();

        $this->incidentManagerMock = $this->createIncidentManagerMock();

        $this->getContainer()->offsetSet('scheduled_maintenance', $this->getSMaintenanceMock());

        $this->getContainer()->offsetSet('component.manager', $this->getComponentManagerMock());

        $this->getContainer()->offsetSet('incident.manager', $this->getIncidentManagerMock());
    }

    /**
     * @return Kernel
     */
    protected function getKernel()
    {
        if (null === $this->kernel) {
            $this->kernel = new Kernel(self::TEST_ENV);
        }

        return $this->kernel;
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IncidentManager
     */
    protected function getIncidentManagerMock()
    {
        return $this->incidentManagerMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ScheduledMaintenance
     */
    protected function getSMaintenanceMock()
    {
        return $this->sMaintenanceMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ComponentManager
     */
    protected function getComponentManagerMock()
    {
        return $this->componentManagerMock;
    }

    /**
     * @return InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getInputInterfaceMock()
    {
        return $this->inputInterface;
    }

    /**
     * @return OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOutputInterfaceMock()
    {
        return $this->outputInterface;
    }

    /**
     * @return IncidentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createIncidentManagerMock()
    {


        $entityManager = static::getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $incidentRepository =  static::getMockBuilder(IncidentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $incidentRepository
            ->method('findOneBy')
            ->willReturn($this->createIncident());

        $entityManager->method('getRepository')->willReturn($incidentRepository);

        $IncidentManagerMock = $this
            ->getMockBuilder(IncidentManager::class)
            ->setConstructorArgs([
                $entityManager,
                $this->getContainer()['incident.checks_type_resolver'],

            ])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $IncidentManagerMock;
    }

    /**
     * @return ComponentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createComponentManagerMock()
    {
        $mockClient = new MockClient();

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $streamMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn(
                json_encode($this->createComponentResponse())
            );

        $response->expects($this->once())->method('getBody')->willReturn($streamMock);

        $mockClient->addResponse($response);

        $httpMethodsClient = new HttpMethodsClient(
            $mockClient,
            MessageFactoryDiscovery::find()
        );

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $componentRepositoryMock = $this
            ->getMockBuilder(ComponentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManagerMock
            ->method('getRepository')
            ->willReturn($componentRepositoryMock);

        $componentManagerMock = $this
            ->getMockBuilder(ComponentManager::class)
            ->setConstructorArgs([
                $entityManagerMock,
                $httpMethodsClient,
                $this->getContainer()['rest.cachet.base_url'],
            ])
            ->enableProxyingToOriginalMethods()
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
        $mockClient = new MockClient();

        $streamMock = $this->getMockBuilder(StreamInterface::class)->getMock();

        $streamMock
            ->expects($this->once())
            ->method('getContents')
            ->willReturn(
                sprintf('{"data":[{"status":0,"scheduled_at":"%s","created_at":"%s"}]}',
                    date('Y-m-d H:i:s', strtotime('-3 days')),
                    date('Y-m-d H:i:s', strtotime('-2 days'))
                )
            );

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();

        $response->expects($this->once())->method('getBody')->willReturn($streamMock);

        $mockClient->addResponse($response);

        $httpMethodsClient = new HttpMethodsClient(
            $mockClient,
            MessageFactoryDiscovery::find()
        );

        $sMaintenanceMock = $this
            ->getMockBuilder(ScheduledMaintenance::class)
            ->setConstructorArgs([
                $httpMethodsClient,
                $this->getContainer()['rest.cachet.base_url'],
            ])
            ->enableProxyingToOriginalMethods()
            ->getMock();

        return $sMaintenanceMock;
    }

    protected function performChecks()
    {
        /** @var Checker $checker */
        $checker = $this->getContainer()['checker'];

        $checker->performChecks(
            $this->getInputInterfaceMock(),
            $this->getOutputInterfaceMock()
        );
    }

    /**
     * @param $leftIndents
     */
    protected function filterCheckList($leftIndents)
    {
        /** @var ChecksList $checkList */
        $checkList = $this->getContainer()['checker.checks_list'];
        $indexShift = 0;
        /** @var CheckInterface $check */
        foreach ($checkList as $index => $check) {
            if (false === in_array($check->getIndent(), $leftIndents, false)) {
                $checkList->removeAt($index - $indexShift);
                ++$indexShift;
            }
        }
    }

    /**
     * @return array
     */
    protected function createComponentResponse()
    {
        $testDataObjForI =
        [
            'data' => (object) [
                'id' => 1,
                'name' => 'Component Name',
                'description' => 'Description',
                'link' => '',
                'status' => 1,
                'order' => 0,
                'group_id' => 0,
                'created_at' => '2015-08-01 12:00:00',
                'updated_at' => '2015-08-01 12:00:00',
                'deleted_at' => null,
                'status_name' => 'Operational',
                'tags' => [
                    'slug-of-tag' => 'Tag Name',
                ],
            ],
            'errors' => [
            ],
        ];

        return $testDataObjForI;
    }

    /**
     * @return Incident
     */
    protected function createIncident()
    {
        $incident = new Incident(
            'test.incident.ident',
            'test_name-foo'
        );
        $incident->setMessage('Some check catch some error');
        $incident->setStatus(32);
        $incident->setType('urgent');

        return $incident;
    }
}
