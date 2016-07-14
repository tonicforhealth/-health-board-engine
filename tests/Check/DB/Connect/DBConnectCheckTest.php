<?php

namespace TonicHealthCheck\Tests\Check\DB\Connect;

use PDOException;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TonicHealthCheck\Check\DB\Connect\DBConnectCheck;
use TonicHealthCheck\Check\DB\Connect\DBConnectCheckException;
use TonicHealthCheck\Check\DB\PDOFactory;

/**
 * Class DBConnectCheckTest.
 */
class DBConnectCheckTest extends PHPUnit_Framework_TestCase
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
        $checkResult = $this->getDBConnectCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * test check connect Exception.
     */
    public function testCheckConnectException()
    {
        $exceptionMsg = 'PDO Connection Error';

        $this->getPDOFactory()->method('createPDO')->willThrowException(new PDOException($exceptionMsg));

        $checkResult = $this->getDBConnectCheck()->performCheck();
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            DBConnectCheckException::class,
            $checkResult->getError()
        );
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
