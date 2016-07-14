<?php

namespace TonicHealthCheck\Check\DB;

use PDO;
use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractDBCheck.
 */
abstract class AbstractDBCheck extends AbstractCheck
{
    const COMPONENT = 'db';
    const GROUP = 'web';

    /**
     * @var PDO
     */
    protected $PDOInstance = null;

    /**
     * @var PDOFactory
     */
    protected $PDOFactory;

    /**
     * @return PDO
     */
    public function getPDOInstance()
    {
        if (null === $this->PDOInstance) {
            $this->PDOInstance = $this->getPDOFactory()->createPDO();
        }

        return $this->PDOInstance;
    }

    /**
     * @return PDOFactory
     */
    public function getPDOFactory()
    {
        return $this->PDOFactory;
    }

    /**
     * @param PDOFactory $PDOFactory
     */
    protected function setPDOFactory(PDOFactory $PDOFactory)
    {
        $this->PDOFactory = $PDOFactory;
    }
}
