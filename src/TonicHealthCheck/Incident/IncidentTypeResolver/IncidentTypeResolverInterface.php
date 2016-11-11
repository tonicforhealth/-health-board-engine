<?php

namespace TonicHealthCheck\Incident\IncidentTypeResolver;

use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType\IncidentTypeOptionsInterface;

/**
 * Class resolve type for the incident object.
 */
interface IncidentTypeResolverInterface
{
    /**
     * @param string $componentCheckIdent
     *
     * @return string
     */
    public function resolveChecksIncidentType($componentCheckIdent);

    /**
     * @param IncidentTypeOptionsInterface $incidentTypeOptions
     */
    public function registerChecksIncidentTypeOptions(
        IncidentTypeOptionsInterface $incidentTypeOptions
    );
}
