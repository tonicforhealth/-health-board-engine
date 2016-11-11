<?php

namespace TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType;

use Collections\Collection;

/**
 * Collection of IncidentTypeOptionsInterface items.
 */
class IncidentTypeOptionsCollection extends Collection
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct(IncidentTypeOptionsInterface::class);
    }
}
