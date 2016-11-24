<?php

namespace TonicHealthCheck\Incident;

use Doctrine\ORM\EntityManager;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\CheckInterface;
use TonicHealthCheck\Entity\Incident;
use TonicHealthCheck\Entity\IncidentStat;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentTypeResolver;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentTypeResolverInterface;

/**
 * Class IncidentManager.
 */
class IncidentManager
{
    /**
     * @var EntityManager
     */
    protected $doctrine;

    /**
     * @var IncidentTypeResolver
     */
    private $incidentTypeResolver;

    /**
     * IncidentHandler constructor.
     *
     * @param EntityManager                 $doctrine
     * @param IncidentTypeResolverInterface $checksITypeMapper
     */
    public function __construct(
        EntityManager $doctrine,
        IncidentTypeResolverInterface $checksITypeMapper
    ) {
        $this->setDoctrine($doctrine);
        $this->setIncidentTypeResolver($checksITypeMapper);
    }

    /**
     * @param CheckInterface $checkObj
     * @param CheckException $exception
     */
    public function fireIncident(CheckInterface $checkObj, CheckException $exception)
    {
        $incidentIdent = $checkObj->getIndent();
        $incidentName = $checkObj->getCheckComponent().':'.$checkObj->getCheckNode();
        $incidentMessage = $exception->getMessage();
        $incidentStatus = $exception->getCode();
        /** @var IncidentInterface $incident */
        $incident = $this->getDoctrine()
            ->getRepository(Incident::class)
            ->findNotResolved($incidentIdent);

        if (!$incident) {
            $incident = new Incident($incidentIdent, $incidentName);
            $incident->setMessage($incidentMessage);

            $this->getDoctrine()->persist($incident);
            $this->getDoctrine()->flush();
        }

        $incident->setStatus($incidentStatus);

        $incidentStat = $this->registerIncidentStat($incident);

        $this->resolveIncidentType($incident, $incidentStat);
    }

    /**
     * @param CheckInterface $checkObj
     */
    public function resolveIncident(CheckInterface $checkObj)
    {
        $ident = $checkObj->getIndent();

        $incident = $this->getDoctrine()
            ->getRepository(Incident::class)
            ->findNotResolved($ident);

        if ($incident && $incident instanceof Incident) {
            $incident->setStatus(IncidentInterface::STATUS_OK);
            $incident->setResolved(true);
            $this->registerIncidentStat($incident);
            $this->getDoctrine()->persist($incident);
            $this->getDoctrine()->flush();
        }
    }

    /**
     * @return EntityManager
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return IncidentTypeResolverInterface
     */
    public function getIncidentTypeResolver()
    {
        return $this->incidentTypeResolver;
    }

    /**
     * @param EntityManager $doctrine
     */
    protected function setDoctrine(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param IncidentTypeResolverInterface $incidentTypeResolver
     */
    protected function setIncidentTypeResolver(IncidentTypeResolverInterface $incidentTypeResolver)
    {
        $this->incidentTypeResolver = $incidentTypeResolver;
    }

    /**
     * @param Incident $incident
     *
     * @return IncidentStat
     */
    protected function registerIncidentStat($incident)
    {
        $incidentStat = new IncidentStat($incident->getIdent());
        $incidentStat->setIncident($incident);
        $incidentStat->setType($incident->getType());
        $incidentStat->setMessage($incident->getMessage());
        $incidentStat->setStatus($incident->getStatus());
        $incidentStat->setResolved($incident->isResolved());
        $this->getDoctrine()->persist($incidentStat);
        $this->getDoctrine()->flush($incidentStat);

        return $incidentStat;
    }

    /**
     * @param Incident     $incident
     * @param IncidentStat $incidentStat
     */
    protected function resolveIncidentType(Incident $incident, IncidentStat $incidentStat)
    {
        $incidentType = $this->getIncidentTypeResolver()->resolveChecksIncidentType($incident->getIdent());
        if ($incident->getType() != IncidentInterface::TYPE_URGENT) {
            $incident->setType($incidentType);
        }
        $incidentStat->setType($incidentType);
        $this->getDoctrine()->persist($incident);
        $this->getDoctrine()->persist($incidentStat);
        $this->getDoctrine()->flush();
    }
}
