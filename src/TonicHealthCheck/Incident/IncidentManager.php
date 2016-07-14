<?php

namespace TonicHealthCheck\Incident;

use Doctrine\ORM\EntityManager;
use TonicHealthCheck\Check\CheckException;
use TonicHealthCheck\Check\CheckInterface;
use TonicHealthCheck\Entity\Incident;

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
     * @var ChecksIncidentTypeMapperInterface
     */
    private $checksIncidentTypeMapper;

    /**
     * IncidentHandler constructor.
     *
     * @param EntityManager                     $doctrine
     * @param ChecksIncidentTypeMapperInterface $checksITypeMapper
     */
    public function __construct(
        EntityManager $doctrine,
        ChecksIncidentTypeMapperInterface $checksITypeMapper
    ) {
        $this->setDoctrine($doctrine);
        $this->setChecksIncidentTypeMapper($checksITypeMapper);
    }

    /**
     * @param CheckInterface $checkObj
     * @param CheckException $exception
     */
    public function fireIncident(CheckInterface $checkObj, CheckException $exception)
    {
        $ident = $checkObj->getIndent();
        $name = $checkObj->getCheckComponent().":".$checkObj->getCheckNode();
        /** @var IncidentInterface $incident */

        $incident = $this->getDoctrine()
            ->getRepository('TonicHealthCheck\Entity\Incident')
            ->findOneBy(['ident' => $ident]);
        if (!$incident) {
            $incident = new Incident($ident, $name);
            $incident->setMessage($exception->getMessage());
            $incident->setType($this->getChecksIncidentTypeMapper()->getChecksIncidentType($checkObj->getIndent()));
            $this->getDoctrine()->persist($incident);
            $this->getDoctrine()->flush();
        }

        $incident->setStatus($exception->getCode());

        $this->getDoctrine()->persist($incident);
        $this->getDoctrine()->flush();
    }

    /**
     * @param CheckInterface $checkObj
     */
    public function resolveIncident(CheckInterface $checkObj)
    {
        $ident = $checkObj->getIndent();

        $incident = $this->getDoctrine()
            ->getRepository('TonicHealthCheck\Entity\Incident')
            ->findOneBy(['ident' => $ident]);
        if ($incident && $incident instanceof Incident) {
            $incident->setStatus(IncidentInterface::STATUS_OK);
            $this->getDoctrine()->persist($incident);
            $this->getDoctrine()->flush();
            $this->getDoctrine()->remove($incident);
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
     * @return ChecksIncidentTypeMapperInterface
     */
    public function getChecksIncidentTypeMapper()
    {
        return $this->checksIncidentTypeMapper;
    }

    /**
     * @param EntityManager $doctrine
     */
    protected function setDoctrine(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param ChecksIncidentTypeMapperInterface $checksITypeMapper
     */
    protected function setChecksIncidentTypeMapper(ChecksIncidentTypeMapperInterface $checksITypeMapper)
    {
        $this->checksIncidentTypeMapper = $checksITypeMapper;
    }
}
