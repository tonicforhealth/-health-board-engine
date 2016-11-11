<?php

namespace TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType;

use TonicHealthCheck\Incident\IncidentInterface;

/**
 * List of the IncidentType options.
 */
class IncidentTypeOptions implements IncidentTypeOptionsInterface
{
    /**
     * Ident of an incident.
     *
     * @var string
     */
    private $ident;

    /**
     * Type of an incident.
     *
     * @var string
     */
    private $type;

    /**
     * Number of the occurrence to fire high level incident an event.
     *
     * @var int
     */
    private $occurrence;

    /**
     * Period of time in sec.
     *
     * @var int
     */
    private $occurrencePeriod;

    /**
     * Period of time in sec.
     *
     * @var int
     */
    private $infoTypeToFire = IncidentInterface::TYPE_MINOR;

    /**
     * IncidentTypeOptions constructor.
     *
     * @param string $ident
     * @param string $type
     */
    public function __construct($ident, $type)
    {
        $this->setIdent($ident);
        $this->setType($type);
    }

    /**
     * @return string
     */
    public function getIdent()
    {
        return $this->ident;
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

    /**
     * @return int
     */
    public function getOccurrence()
    {
        return $this->occurrence;
    }

    /**
     * @param int $occurrence
     */
    public function setOccurrence($occurrence)
    {
        $this->occurrence = $occurrence;
    }

    /**
     * @return int
     */
    public function getOccurrencePeriod()
    {
        return $this->occurrencePeriod;
    }

    /**
     * @param int $occurrencePeriod
     */
    public function setOccurrencePeriod($occurrencePeriod)
    {
        $this->occurrencePeriod = $occurrencePeriod;
    }

    /**
     * @return int
     */
    public function getInfoTypeToFire()
    {
        return $this->infoTypeToFire;
    }

    /**
     * @param int $infoTypeToFire
     */
    public function setInfoTypeToFire($infoTypeToFire)
    {
        $this->infoTypeToFire = $infoTypeToFire;
    }

    /**
     * @param string $ident
     */
    protected function setIdent($ident)
    {
        $this->ident = $ident;
    }
}
