<?php

namespace TonicHealthCheck\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use SplObserver;
use TonicHealthCheck\Incident\IncidentInterface;

/**
 * TonicHealthCheck\Entity\Incident;.
 *
 * @ExclusionPolicy("none")
 * @HasLifecycleCallbacks
 * @Entity(repositoryClass="IncidentRepository")
 * @Table(name="incident", indexes={@Index(name="search_incident_1", columns={"ident", "resolved"})})
 */
class Incident implements IncidentInterface
{
    /**
     * @OneToMany(targetEntity="IncidentStat", mappedBy="incident", cascade={"remove"})
     */
    private $incidentStats;

    /**
     * @var array
     * @Exclude
     */
    private $observers = array();

    /**
     * @Id
     * @Exclude
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(type="integer", nullable=true)
     */
    private $external_id;

    /**
     * @Column(type="string", length=128)
     * @Expose
     */
    private $ident;

    /**
     * Type of the problems (urgent, warning, minor).
     *
     * @Column(type="string", length=32, name="`type`", nullable=true)
     * @Expose
     * @Type("string")
     */
    private $type = self::TYPE_URGENT;

    /**
     * @Column(type="string", length=128)
     * @Expose
     * @Type("string")
     */
    private $name;

    /**
     * @Column(type="text")
     * @Expose
     * @Type("string")
     */
    private $message;

    /**
     * @Column(type="integer", nullable=true)
     * @Expose
     * @Type("integer")
     */
    private $status;

    /**
     * @var DateTimeInterface
     * @Column(type="datetime", name="create_at")
     */
    private $createAt;

    /**
     * @var DateTimeInterface
     * @Column(type="datetime", name="update_at", nullable=true)
     */
    private $updateAt;

    /**
     * @Column(type="boolean")
     */
    private $resolved = false;

    /**
     * Incident constructor.
     *
     * @param string $ident
     * @param string $name
     */
    public function __construct($ident, $name = '')
    {
        $this->setIdent($ident);
        $this->setName($name);
    }

    /**
     * @param IncidentStat $incidentStat
     */
    public function addIncidentStats(IncidentStat $incidentStat)
    {
        $this->incidentStats[] = $incidentStat;
    }
    /**
     * @return Collection
     */
    public function getIncidentStats()
    {
        return $this->incidentStats;
    }

    /**
     * Attach an SplObserver.
     *
     * @link http://php.net/manual/en/splsubject.attach.php
     *
     * @param SplObserver $observer <p>
     *                              The <b>SplObserver</b> to attach.
     *                              </p>
     *
     * @since 5.1.0
     */
    public function attach(SplObserver $observer)
    {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    /**
     * Detach an observer.
     *
     * @link http://php.net/manual/en/splsubject.detach.php
     *
     * @param SplObserver $observer <p>
     *                              The <b>SplObserver</b> to detach.
     *                              </p>
     *
     * @since 5.1.0
     */
    public function detach(SplObserver $observer)
    {
        $key = spl_object_hash($observer);

        if (isset($this->observers[$key])) {
            unset($this->observers[$key]);
        }
    }

    /**
     * Notify an observer.
     *
     * @link http://php.net/manual/en/splsubject.notify.php
     * @since 5.1.0
     */
    public function notify()
    {
        /** @var \SplObserver $value */
        foreach ($this->observers as $value) {
            $value->update($this);
        }
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return Incident
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ident.
     *
     * @param string $ident
     *
     * @return Incident
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;

        return $this;
    }

    /**
     * Get ident.
     *
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Incident
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return Incident
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Incident
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set externalId.
     *
     * @param int $externalId
     *
     * @return Incident
     */
    public function setExternalId($externalId)
    {
        $this->external_id = $externalId;

        return $this;
    }

    /**
     * Get externalId.
     *
     * @return int
     */
    public function getExternalId()
    {
        return $this->external_id;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @return DateTimeInterface
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * @return bool
     */
    public function isResolved()
    {
        return $this->resolved;
    }

    /**
     * @param bool $resolved
     */
    public function setResolved($resolved)
    {
        $this->resolved = $resolved;
    }

    /**
     * @PrePersist
     */
    public function doStuffOnPrePersist()
    {
        $this->createAt = new DateTime();
    }

    /**
     * @PreUpdate
     */
    public function doStuffOnPreUpdate()
    {
        $this->updateAt = new DateTime();
    }
}
