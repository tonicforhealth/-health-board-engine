<?php

namespace TonicHealthCheck\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class IncidentRepository.
 */
class IncidentRepository extends EntityRepository
{
    /**
     * @param string $ident
     *
     * @return null|object
     */
    public function findNotResolved($ident)
    {
        return $this->findOneBy([
                'ident' => $ident,
                'resolved' => false,
            ]);
    }
}
