<?php

namespace TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType;

/**
 * List of the IncidentType options.
 */
interface IncidentTypeOptionsInterface
{
    /**
     * @return string
     */
    public function getIdent();

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getOccurrence();

    /**
     * @param int $occurrence
     */
    public function setOccurrence($occurrence);

    /**
     * @return int
     */
    public function getOccurrencePeriod();

    /**
     * @param int $occurrencePeriod
     */
    public function setOccurrencePeriod($occurrencePeriod);

    /**
     * @return int
     */
    public function getInfoTypeToFire();

    /**
     * @param int $infoTypeToFire
     */
    public function setInfoTypeToFire($infoTypeToFire);
}
