<?php

namespace TonicHealthCheck\Tests\Check\DB\Connect;

use PDO;
use PDOException;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TonicHealthCheck\Check\DB\BaseTableExist\DBBaseTableExistCheck;
use TonicHealthCheck\Check\DB\DBExpectConnectCheckException;
use TonicHealthCheck\Check\DB\PDOFactory;

/**
 * Class DBConnectCheckTest.
 */
class DBBaseTableExistCheckTest extends PHPUnit_Framework_TestCase
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
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function setUp()
    {
        $PDOFactory = new PDOFactory(
            'sqlite::memory:',
            null,
            null
        );

        $this->setDBBaseTableExistCheckBuilder(
            $this
                ->getMockBuilder(DBBaseTableExistCheck::class)
                ->setConstructorArgs([
                    'testnode',
                    $PDOFactory,
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
     * Test expect connect.
     */
    public function testCheckExpectConnectException()
    {
        $exceptionMsg = 'PDO Connection Error';
        $exceptionCode = 1343;

        $this->getDBBaseTableExistCheck()
            ->method('getPDOInstance')
            ->willThrowException(new PDOException($exceptionMsg, $exceptionCode));

        $checkResult = $this->getDBBaseTableExistCheck()->performCheck();

        $this->assertEquals($exceptionCode, $checkResult->getError()->getCode());
        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            DBExpectConnectCheckException::class,
            $checkResult->getError()
        );
    }

    /**
     * Test expect connect null.
     */
    public function testCheckExpectConnectNullException()
    {
        $PDOFactory = new PDOFactory(
            'sqlite::memory:',
            null,
            null
        );

        $dBBaseTableExistCheck = $this->getDBBaseTableExistCheckBuilder()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $dBBaseTableExistCheck
            ->method('getPDOInstance')
            ->willReturn(null);

        $checkResult = $dBBaseTableExistCheck->performCheck();

        $this->assertInstanceOf(
            DBExpectConnectCheckException::class,
            $checkResult->getError()
        );
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
}
