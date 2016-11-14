<?php

namespace TonicHealthCheck\Entity;

use DateTime;
use DateTimeInterface;

/**
 * TonicHealthCheck\Entity\Incident;
 *
 * @ExclusionPolicy("none")
 * @HasLifecycleCallbacks
 * @Entity(repositoryClass="IncidentStatRepository")
 * @Table(name="incident_stat", indexes={@Index(name="search_incident_stat_1", columns={"ident", "create_at"})})
 */
class IncidentStat
{
    /**
     * @var Incident
     * @ManyToOne(targetEntity="Incident", inversedBy="incident_stat")
     * @JoinColumn(name="incident_id", referencedColumnName="id", nullable=FALSE)
     */
    private $incident;

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
    private $type;

    /**
     * @var DateTimeInterface
     * @Column(type="datetime", name="create_at")
     */
    private $createAt;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     * @Expose
     * @Type("string")
     */
    private $message;

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     * @Expose
     * @Type("integer")
     */
    private $status;

    /**
     * @Column(type="boolean")
     */
    private $resolved;

    /**
     * IncidentStat constructor.
     *
     * @param string $ident
     */
    public function __construct($ident)
    {
        $this->setIdent($ident);
    }

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
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * @param mixed $ident
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * @PrePersist
     */
    public function doStuffOnPrePersist()
    {
        $this->createAt = new DateTime();

    }

    /**
     * @return Incident
     */
    public function getIncident()
    {
        return $this->incident;
    }

    /**
     * @param Incident $incident
     */
    public function setIncident(Incident $incident)
    {
        $this->incident = $incident;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
