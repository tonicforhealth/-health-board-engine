<?php

namespace TonicHealthCheck\Check\DB;

use PDO;

/**
 * Interface PDOFactoryInterface.
 */
interface PDOFactoryInterface
{
    /**
     * PDOFactoryInterface constructor.
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     */
    public function __construct($dsn, $user, $password);
    /**
     * @return PDO
     */
    public function createPDO();
}
