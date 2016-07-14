<?php

namespace TonicHealthCheck\Check\DB\BaseTableExist;

use PDO;
use PDOException;
use TonicHealthCheck\Check\DB\AbstractDBCheck;
use TonicHealthCheck\Check\DB\DBExpectConnectCheckException;
use TonicHealthCheck\Check\DB\PDOFactory;

/**
 * Class DBBaseTableExistCheck.
 */
class DBBaseTableExistCheck extends AbstractDBCheck
{
    const CHECK = 'db-base-table-exist-check';
    const DEFAULT_SQL_GET_ALL_TABLE = 'SHOW TABLES';

    /**
     * @var array
     */
    protected $tablesExist;

    /**
     * @var string
     */
    protected $sqlGetAllTable;

    /**
     * @param string     $checkNode
     * @param PDOFactory $PDOFactory
     * @param array      $tablesExist
     * @param string     $sqlGetAllTable
     */
    public function __construct(
        $checkNode,
        PDOFactory $PDOFactory,
        $tablesExist,
        $sqlGetAllTable = self::DEFAULT_SQL_GET_ALL_TABLE
    ) {
        parent::__construct($checkNode);
        $this->setPDOFactory($PDOFactory);
        $this->setTablesExist($tablesExist);
        $this->setSqlGetAllTable($sqlGetAllTable);
    }

    /**
     * Check $tablesExist in the database.
     *
     * @param null|array $tablesExist
     *
     * @return bool|void
     *
     * @throws DBBaseTableExistCheckException
     * @throws DBExpectConnectCheckException
     */
    public function check($tablesExist = null)
    {
        if (null === $tablesExist) {
            $tablesExist = $this->getTablesExist();
        }
        try {
            $pdo = $this->getPDOInstance();

            if (null === $pdo || !$this->getPDOInstance() instanceof PDO) {
                throw DBExpectConnectCheckException::expectConnected();
            }
        } catch (PDOException $e) {
            throw new DBExpectConnectCheckException($e->getMessage(), $e->getCode());
        }

        try {
            $sql = $this->getSqlGetAllTable();
            $query = $this->getPDOInstance()->query($sql);
            $tables = $query->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tablesExist as $tableName) {
                if (array_search($tableName, $tables) === false) {
                    throw DBBaseTableExistCheckException::tableDoesNotExist($tableName);
                }
            }
        } catch (\PDOException $e) {
            throw new DBBaseTableExistCheckException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return array
     */
    public function getTablesExist()
    {
        return $this->tablesExist;
    }

    /**
     * @param array $tablesExist
     */
    protected function setTablesExist($tablesExist)
    {
        $this->tablesExist = $tablesExist;
    }

    /**
     * @return string
     */
    public function getSqlGetAllTable()
    {
        return $this->sqlGetAllTable;
    }

    /**
     * @param string $sqlGetAllTable
     */
    public function setSqlGetAllTable($sqlGetAllTable)
    {
        $this->sqlGetAllTable = $sqlGetAllTable;
    }
}
