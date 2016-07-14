<?php

namespace TonicHealthCheck\Tests\Check\DB\Connect;

use PDOException;
use PDOStatement;
use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_TestCase;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use TonicHealthCheck\Check\DB\BaseTableExist\DBBaseTableExistCheck;
use TonicHealthCheck\Check\DB\BaseTableExist\DBBaseTableExistCheckException;
use TonicHealthCheck\Check\DB\PDOFactory;
use TonicHealthCheck\Tests\Check\DB\PDOMock;

/**
 * Class DBConnectCheckTest.
 */
class DBBaseTableExistCheckDbTest extends PHPUnit_Extensions_Database_TestCase
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
                        'users',

                    ],
                    'SELECT name FROM sqlite_master WHERE type = "table"',
                ])
                ->enableProxyingToOriginalMethods()
                ->setMethods(['getPDOInstance'])
        );
        $this->setDBBaseTableExistCheck($this->getDBBaseTableExistCheckBuilder()->getMock());
        $this->createBaseScheme();
        parent::setUp();
    }

    /**
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    public function getConnection()
    {
        return $this->createDefaultDBConnection(
            $this->getDBBaseTableExistCheck()->getPDOInstance(),
            ':memory:'
        );
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__).'/DataFixtures/base_tables.xml');
    }

    /**
     * Test is ok.
     */
    public function testCheckIsOk()
    {
        $checkResult = $this->getDBBaseTableExistCheck()->performCheck();

        $this->assertTrue($checkResult->isOk());
        $this->assertNull($checkResult->getError());
    }

    /**
     * Test is base tables do not exist.
     */
    public function testCheckBaseTablesDoNotExist()
    {
        $dBBaseTableExistCheckBuilder = $this->getDBBaseTableExistCheckBuilder()
           ->getMock();

        $checkResult = $dBBaseTableExistCheckBuilder->performCheck();

        $this->assertFalse($checkResult->isOk());
        $this->assertInstanceOf(
            DBBaseTableExistCheckException::class,
            $checkResult->getError()
        );

        $this->assertEquals(
            DBBaseTableExistCheckException::CODE_TABLE_DONT_EXIST,
            $checkResult->getError()->getCode()
        );
    }

    /**
     * Test is base tables do not exist.
     */
    public function testPDOFetchAllExeption()
    {
        $exceptionMsg = 'PDO Error when getting data.';
        $exceptionCode = 4334;

        $dBBaseTableExistCheckBuilder = $this->getDBBaseTableExistCheckBuilder()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $PDOStatement = $this->getMockBuilder(PDOStatement::class)
            ->setMethods(['fetchAll'])
            ->getMock();

        $PDOStatement
            ->method('fetchAll')
            ->willThrowException(
                new PDOException(
                    $exceptionMsg,
                    $exceptionCode
                )
            );

        $PDOMock = $this->getMockBuilder(PDOMock::class)
            ->setMethods(['query'])
            ->getMock();

        $PDOMock->method('query')->willReturn($PDOStatement);

        $dBBaseTableExistCheckBuilder
            ->method('getPDOInstance')
            ->willReturn($PDOMock);

        $checkResult = $dBBaseTableExistCheckBuilder->performCheck();

        $this->assertFalse($checkResult->isOk());

        $this->assertEquals($exceptionCode, $checkResult->getError()->getCode());

        $this->assertStringEndsWith($exceptionMsg, $checkResult->getError()->getMessage());
        $this->assertInstanceOf(
            DBBaseTableExistCheckException::class,
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
     * Create base shema.
     */
    protected function createBaseScheme()
    {
        $fixtureDataSet = $this->getDataSet();

        foreach ($fixtureDataSet->getTableNames() as $table) {
            // drop table
            $this->getDBBaseTableExistCheck()->getPDOInstance()->exec("DROP TABLE IF EXISTS `$table`;");
            // recreate table
            $meta = $fixtureDataSet->getTableMetaData($table);
            $create = "CREATE TABLE IF NOT EXISTS `$table` ";
            $cols = array();
            foreach ($meta->getColumns() as $col) {
                $cols[] = "`$col` VARCHAR(200)";
            }
            $create .= '('.implode(',', $cols).');';
            $this->getDBBaseTableExistCheck()->getPDOInstance()->exec($create);
        }
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
