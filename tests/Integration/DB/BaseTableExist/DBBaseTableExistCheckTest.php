<?php

namespace TonicHealthCheck\Tests\Integration\DB\Connect;

use PDOStatement;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\DB\BaseTableExist\DBBaseTableExistCheck;
use TonicHealthCheck\Check\DB\PDOFactory;
use TonicHealthCheck\Tests\Check\DB\PDOMock;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class DBConnectCheckTest.
 */
class DBBaseTableExistCheckTest extends AbstractIntegration
{
    /**
     * @var DBBaseTableExistCheck
     */
    private $dBBaseTableExistCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockBuilder
     */
    private $dBBaseTableExistCheckBuilder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $PDOFactory;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function setUp()
    {
        parent::setUp();
        $this->setPDOFactory(
            $this
                ->getMockBuilder(PDOFactory::class)
                ->setConstructorArgs([
                    'sqlite::memory:',
                    null,
                    null,
                ]
                )->getMock()
        );

        $this->setDBBaseTableExistCheckBuilder(
            $this
                ->getMockBuilder(DBBaseTableExistCheck::class)
                ->setConstructorArgs([
                    'testnode',
                    $this->getPDOFactory(),
                    [
                        'articles',
                    ],
                ])
                ->enableProxyingToOriginalMethods()
                ->setMethods(['getPDOInstance'])
        );

        $this->setDBBaseTableExistCheck($this->getDBBaseTableExistCheckBuilder()->getMock());
    }

    /**
     * Test db engine service doesn't have app tables.
     */
    public function testDoesNotHaveAppTables()
    {
        $pdo = $this
            ->getMockBuilder(PDOMock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdoStatement = $this->getMockBuilder(PDOStatement::class)->getMock();

        $pdoStatement
            ->method('fetchAll')
            ->willReturn(['patient_profile', 'articles_test']);

        $pdo
            ->method('query')
            ->willReturn($pdoStatement);

        $this
            ->getPDOFactory()
            ->method('createPDO')
            ->willReturn($pdo);

        $this->getChecksList()->add($this->getDBBaseTableExistCheck());

        $this->expectFireIncident(
            function (CheckException $exception) {
                $this->assertEquals(2002, $exception->getCode());
                $this->assertContains('DBBaseTableExistCheck: Table: articles don\'t exist into db', $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getPDOFactory()
    {
        return $this->PDOFactory;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getDBBaseTableExistCheck()
    {
        return $this->dBBaseTableExistCheck;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function getDBBaseTableExistCheckBuilder()
    {
        return $this->dBBaseTableExistCheckBuilder;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $dBBaseTableExistCheck
     */
    protected function setDBBaseTableExistCheck(PHPUnit_Framework_MockObject_MockObject $dBBaseTableExistCheck)
    {
        $this->dBBaseTableExistCheck = $dBBaseTableExistCheck;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockBuilder $dBBaseTableExistCheckBuilder
     */
    protected function setDBBaseTableExistCheckBuilder($dBBaseTableExistCheckBuilder)
    {
        $this->dBBaseTableExistCheckBuilder = $dBBaseTableExistCheckBuilder;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $PDOFactory
     */
    protected function setPDOFactory(PHPUnit_Framework_MockObject_MockObject $PDOFactory)
    {
        $this->PDOFactory = $PDOFactory;
    }
}
