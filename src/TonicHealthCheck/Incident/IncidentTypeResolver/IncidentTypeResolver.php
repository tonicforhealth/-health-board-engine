<?php

namespace TonicHealthCheck\Incident\IncidentTypeResolver;

use Doctrine\ORM\EntityManager;
use TonicHealthCheck\Entity\IncidentStat;
use TonicHealthCheck\Entity\IncidentStatRepository;
use TonicHealthCheck\Incident\IncidentInterface;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType\IncidentTypeOptionsCollection;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType\IncidentTypeOptionsInterface;

/**
 * Class resolve type for the incident object.
 */
class IncidentTypeResolver implements IncidentTypeResolverInterface
{
    /**
     * @var EntityManager
     */
    protected $doctrine;

    /**
     * @var IncidentTypeOptionsCollection
     */
    protected $incidentTypeC;

    /**
     * IncidentTypeResolver constructor.
     *
     * @param EntityManager                 $doctrine
     * @param IncidentTypeOptionsCollection $incidentTypeC
     */
    public function __construct(
        EntityManager $doctrine,
        IncidentTypeOptionsCollection $incidentTypeC = null
    ) {
        $this->doctrine = $doctrine;

        if (null === $incidentTypeC) {
            $incidentTypeC = new IncidentTypeOptionsCollection();
        }
        $this->incidentTypeC = $incidentTypeC;
    }

    /**
     * @param string $componentCheckIdent
     *
     * @return string
     */
    public function resolveChecksIncidentType($componentCheckIdent)
    {
        $checksIncidentType = IncidentInterface::TYPE_URGENT;
        $incidentTypeOptions = $this->incidentTypeC->find(
            $this->getIncidentFindCallback($componentCheckIdent)
        );

        if ($incidentTypeOptions instanceof IncidentTypeOptionsInterface) {
            $checksIncidentType = $this->resolveTypeUsingOptions($componentCheckIdent, $incidentTypeOptions);
        }

        return $checksIncidentType;
    }

    /**
     * @param IncidentTypeOptionsInterface $incidentTypeOptions
     */
    public function registerChecksIncidentTypeOptions(IncidentTypeOptionsInterface $incidentTypeOptions)
    {
        $this->incidentTypeC->add($incidentTypeOptions);
    }

    /**
     * @param string                       $componentCheckIdent
     * @param IncidentTypeOptionsInterface $incidentTypeOptions
     *
     * @return mixed
     */
    protected function resolveTypeUsingOptions($componentCheckIdent, IncidentTypeOptionsInterface $incidentTypeOptions)
    {
        $checksIncidentType = $incidentTypeOptions->getInfoTypeToFire();

        if ($incidentTypeOptions->getOccurrence() > 1) {
            /** @var IncidentStatRepository $incidentStatRep */
            $incidentStatRep = $this->doctrine->getRepository(IncidentStat::class);
            $occurrence = $incidentStatRep->getOccurrencePastPeriod(
                $componentCheckIdent,
                $incidentTypeOptions->getOccurrencePeriod()
            );
            if ($occurrence >= $incidentTypeOptions->getOccurrence()) {
                $checksIncidentType = $incidentTypeOptions->getType();
            }
        }

        return $checksIncidentType;
    }

    /**
     * @param $componentCheckIdent
     *
     * @return \Closure
     */
    protected function getIncidentFindCallback($componentCheckIdent)
    {
        return function (IncidentTypeOptionsInterface $item) use ($componentCheckIdent) {
            return $item->getIdent() == $componentCheckIdent;
        };
    }
}
