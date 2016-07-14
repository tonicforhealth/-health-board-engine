<?php

namespace TonicHealthCheck\Incident;

use TonicHealthCheck\Incident\IncidentInterface;

/**
 * Class ChecksIncidentTypeMapper.
 */
class ChecksIncidentTypeMapper implements ChecksIncidentTypeMapperInterface
{
    /**
     * @var array
     */
    private $checksIncidentTypeMap = [];

    /**
     * ChecksIncidentTypeMapper constructor.
     *
     * @param array $checksInTypeMap
     */
    public function __construct($checksInTypeMap = [])
    {
        $this->setChecksIncidentTypeMap($checksInTypeMap);
    }

    /**
     * @param string $componentCheckIdent
     *
     * @return string
     */
    public function getChecksIncidentType($componentCheckIdent)
    {
        $checksIncidentType = IncidentInterface::TYPE_URGENT;

        if (isset($this->checksIncidentTypeMap[$componentCheckIdent])) {
            $checksIncidentType = $this->checksIncidentTypeMap[$componentCheckIdent];
        }

        return $checksIncidentType;
    }

    /**
     * @param string $componentCheckIdent
     * @param string $incidentType
     */
    public function setChecksIncidentType($componentCheckIdent, $incidentType)
    {
        $this->checksIncidentTypeMap[$componentCheckIdent] = $incidentType;
    }

    /**
     * @param array $checksInTypeMap
     */
    protected function setChecksIncidentTypeMap($checksInTypeMap)
    {
        $this->checksIncidentTypeMap = $checksInTypeMap;
    }
}
