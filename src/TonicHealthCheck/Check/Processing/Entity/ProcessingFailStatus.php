<?php

namespace TonicHealthCheck\Check\Processing\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * TonicHealthCheck\Entity\ProcessingFailStatus;.
 *
 * @Entity(repositoryClass="ProcessingFailStatusRepository")
 * @Table(name="ProcessingFailStatus")
 * @HasLifecycleCallbacks
 */
class ProcessingFailStatus
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="string", length=128)
     */
    private $name;

    /**
     * @var DateTimeInterface
     * @Column(type="datetime", nullable=true)
     */
    private $failAt;

    /**
     * @Column(type="integer")
     */
    private $lastFailStatus;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return DateTimeInterface
     */
    public function getFailAt()
    {
        return $this->failAt;
    }

    /**
     * @param DateTimeInterface $failAt
     */
    public function setFailAt(DateTimeInterface $failAt)
    {
        $this->failAt = $failAt;
    }

    /**
     * @return int
     */
    public function getLastFailStatus()
    {
        return $this->lastFailStatus;
    }

    /**
     * @param int $lastFailStatus
     */
    public function setLastFailStatus($lastFailStatus)
    {
        $this->lastFailStatus = $lastFailStatus;
    }
}
