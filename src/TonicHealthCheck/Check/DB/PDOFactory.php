<?php

namespace TonicHealthCheck\Check\DB;

use PDO;

/**
 * Class PDOFactory.
 */
class PDOFactory implements PDOFactoryInterface
{
    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * PDOFactoryInterface constructor.
     *
     * @param string $dsn
     * @param string $user
     * @param string $password
     */
    public function __construct($dsn, $user, $password)
    {
        $this->setDsn($dsn);
        $this->setUser($user);
        $this->setPassword($password);
    }

    /**
     * @return PDO
     */
    public function createPDO()
    {
        return new PDO($this->getDsn(), $this->getUser(), $this->getPassword());
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $dsn
     */
    protected function setDsn($dsn)
    {
        $this->dsn = $dsn;
    }

    /**
     * @param string $user
     */
    protected function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param string $password
     */
    protected function setPassword($password)
    {
        $this->password = $password;
    }
}
