<?php

namespace TonicHealthCheck\Check\DB\BaseTableExist;

use TonicHealthCheck\Check\DB\DBCheckException;

/**
 * Class DBBaseTableExistCheckException.
 */
class DBBaseTableExistCheckException extends DBCheckException
{
    const EXCEPTION_NAME = 'DBBaseTableExistCheck';

    const CODE_TABLE_DONT_EXIST = 2002;
    const TEXT_TABLE_DONT_EXIST = 'Table: %s don\'t exist into db';

    /**
     * @param string $tableName
     *
     * @return self
     */
    public static function tableDoesNotExist($tableName)
    {
        return new self(sprintf(self::TEXT_TABLE_DONT_EXIST, $tableName), self::CODE_TABLE_DONT_EXIST);
    }
}
