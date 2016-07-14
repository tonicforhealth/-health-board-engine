<?php

namespace TonicHealthCheck\Tests\Integration\DB\Connect;

use PDOException;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\DB\Connect\DBConnectCheck;
use TonicHealthCheck\Check\DB\PDOFactory;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class DBConnectCheckTest.
 */
class DBConnectCheckTest extends AbstractIntegration
{
    /**
     * @var DBConnectCheck
     */
    protected $dBConnectCheck;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $PDOFactory;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $PDO;

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
            ])
            ->enableProxyingToOriginalMethods()
            ->setMethods(['createPDO'])
            ->getMock()
        );

        $this->setDBConnectCheck(new DBConnectCheck(
            'testnode',
            $this->getPDOFactory()
        ));
    }

    /**
     * Is ok test.
     */
    public function testCheckIsOk()
    {
        $this->getChecksList()->add($this->getDBConnectCheck());

        $this->expectResolveIncident();

        $this->performChecks();
    }

    /**
     * Test db engine is down.
     */
    public function testDbEngineIsDown()
    {
        $exceptionMsg = 'SQLSTATE[HY000] [2002] Connection refused';

        $this->getPDOFactory()
            ->method('createPDO')
            ->willThrowException(new PDOException($exceptionMsg));

        $this->getChecksList()->add($this->getDBConnectCheck());

        $this->expectFireIncident(
            function (CheckException $exception) use ($exceptionMsg) {
                $this->assertEquals(-1, $exception->getCode());
                $this->assertContains($exceptionMsg, $exception->getMessage());

                return true;
            }
        );

        $this->performChecks();
    }

    /**
     * @return DBConnectCheck
     */
    public function getDBConnectCheck()
    {
        return $this->dBConnectCheck;
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
    public function getPDO()
    {
        return $this->PDO;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $PDOFactory
     */
    protected function setPDOFactory(PHPUnit_Framework_MockObject_MockObject $PDOFactory)
    {
        $this->PDOFactory = $PDOFactory;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $PDO
     */
    protected function setPDO(PHPUnit_Framework_MockObject_MockObject $PDO)
    {
        $this->PDO = $PDO;
    }

    /**
     * @param DBConnectCheck $dBConnectCheck
     */
    protected function setDBConnectCheck(DBConnectCheck $dBConnectCheck)
    {
        $this->dBConnectCheck = $dBConnectCheck;
    }
}
