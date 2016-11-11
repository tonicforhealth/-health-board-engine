<?php

namespace TonicHealthCheck\Entity;

use DateTime;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

/**
 * Class IncidentRepository.
 */
class IncidentStatRepository extends EntityRepository
{
    /**
     * @param string $indent
     * @param int    $secondsAgo
     *
     * @return int
     */
    public function getOccurrencePastPeriod($indent, $secondsAgo)
    {
        $lastTime = new  DateTime(sprintf('-%s second', $secondsAgo));
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder->select('count(istat.id)')
            ->from($this->getClassName(), 'istat')
            ->where('istat.createAt > :last AND istat.ident = :indent')
            ->setParameter('last', $lastTime, Type::DATETIME)
            ->setParameter('indent', $indent);


        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
