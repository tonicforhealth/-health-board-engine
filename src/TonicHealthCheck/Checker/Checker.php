<?php

namespace TonicHealthCheck\Checker;

use diversen\lang;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TonicHealthCheck\Check\CheckInterface;
use TonicHealthCheck\Check\CheckResult;
use TonicHealthCheck\Component\ComponentManager;
use TonicHealthCheck\Entity\Component;
use TonicHealthCheck\Incident\IncidentInterface;
use TonicHealthCheck\Incident\IncidentManager;
use TonicHealthCheck\Maintenance\scheduledMaintenance;
use Twig_Environment;

/**
 * Class Checker.
 */
class Checker
{
    protected $components = [];
    /**
     * @var ChecksList
     */
    private $checksList;

    /**
     * @var ComponentManager
     */
    private $componentManager;

    /**
     * @var IncidentManager
     */
    private $incidentManager;

    /**
     * @var ScheduledMaintenance;
     */
    private $scheduledMaintenance;

    /**
     * @var Twig_Environment
     */
    private $view;

    /**
     * Checker constructor.
     *
     * @param ChecksList           $checksList
     * @param ComponentManager     $componentManager
     * @param IncidentManager      $incidentManager
     * @param scheduledMaintenance $scheduledMaintenance
     * @param Twig_Environment     $twig
     */
    public function __construct(
        ChecksList $checksList,
        ComponentManager $componentManager,
        IncidentManager $incidentManager,
        ScheduledMaintenance $scheduledMaintenance,
        Twig_Environment $twig
    ) {
        $this->setView($twig);
        $this->setComponentManager($componentManager);
        $this->setScheduledMaintenance($scheduledMaintenance);
        $this->setIncidentManager($incidentManager);
        $this->setChecksList($checksList);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function performChecks(InputInterface $input, OutputInterface $output)
    {
        /* @var CheckInterface $checkI */
        $checkResult = null;
        $ident = null;

        if (!$this->getScheduledMaintenance()->isNowMaintenanceOn()) {
            foreach ($this->getChecksList() as $checkI) {
                $this->renderComponentCheckStart($output, $checkI);
                $checkResult = $this->performCheck($output, $checkI);

                if ($checkResult instanceof CheckResult) {
                    $componentName = $checkI->getCheckComponent();
                    if (!isset($this->components[$componentName])) {
                        $this->components[$componentName] = $this->getComponentManager()->getComponentByName($componentName);
                        $this->components[$componentName]->setStatus(IncidentInterface::STATUS_OK);
                    }
                    /** @var Component $component */
                    $component = $this->components[$componentName];
                    if ($component->getStatus() == IncidentInterface::STATUS_OK) {
                        $component->setStatus($checkResult->getStatus());
                    }
                }
            }

            foreach ($this->components as $component) {
                try {
                    $componentData = $this->getComponentManager()->getComponentRes($component->getId());
                    if (null !== $componentData
                        && empty($componentData->error)
                        && !empty($componentData->data)) {
                        $this->getComponentManager()->updateComponentRes($component);
                    } else {
                        $componentRes = $this->getComponentManager()->createComponentRes($component);
                        $component->setId($componentRes->id);
                    }
                    $this->getComponentManager()->saveComponent($component);
                } catch (\Exception $e) {
                    $this->renderCheckError(
                        $output,
                        'check_sync_error.twig',
                        [
                            'error' => $e->getMessage(),
                        ],
                        OutputInterface::VERBOSITY_NORMAL
                    );
                }
            }
        }
    }

    /**
     * @param Twig_Environment $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return Twig_Environment
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return ChecksList
     */
    public function getChecksList()
    {
        return $this->checksList;
    }

    /**
     * @return IncidentManager
     */
    public function getIncidentManager()
    {
        return $this->incidentManager;
    }

    /**
     * @return ComponentManager
     */
    public function getComponentManager()
    {
        return $this->componentManager;
    }

    /**
     * @return scheduledMaintenance
     */
    public function getScheduledMaintenance()
    {
        return $this->scheduledMaintenance;
    }

    protected function performCheck(
        OutputInterface $output,
        CheckInterface $checkI
    ) {
        $message = lang::translate('TEXT_'.str_replace('-', '_', strtoupper($checkI->getCheckIdent())));
        $checkResult = $checkI->performCheck();

        if ($checkResult->isOk()) {
            $this->renderCheckMessage(
                $output,
                'check_message.twig',
                [
                    'ident' => $checkI->getCheckNode(),
                    'message' => $message,
                    'status' => lang::translate('TEXT_STATUS_OK'),
                    OutputInterface::VERBOSITY_VERBOSE,
                ]
            );
            $this->getIncidentManager()->resolveIncident($checkI);
        } else {
            $this->renderCheckError(
                $output,
                'check_error.twig',
                [
                    'error' => $checkResult->getError()->getMessage(),
                    'code' => $checkResult->getError()->getCode(),
                ],
                OutputInterface::VERBOSITY_VERBOSE
            );

            $this->renderCheckError(
                $output,
                'check_error_debug.twig',
                [
                    'error_debug' => $checkResult->getError()->getNestedDebug(),
                ],
                OutputInterface::VERBOSITY_DEBUG
            );

            $this->renderCheckMessage(
                $output,
                'check_message.twig',
                [
                    'ident' => $checkI->getCheckNode(),
                    'message' => $message,
                    'status' => lang::translate('TEXT_STATUS_FAIL'),
                ]
            );

            $this->getIncidentManager()->fireIncident($checkI, $checkResult->getError());
        }

        return $checkResult;
    }

    protected function renderCheckStart(OutputInterface $output, $message, $indent)
    {
        $this->renderCheckMessage(
            $output,
            'start_end_check.twig',
            [
                'ident' => $indent,
                'message' => $message,
            ]
        );
    }

    protected function renderCheckMessage(OutputInterface $output, $templateName, $context)
    {
        $output->writeln($this->getView()->render($templateName, $context));
    }

    /**
     * @param OutputInterface $output
     * @param $templateName
     * @param $context
     * @param int $verbosity
     */
    protected function renderCheckError(
        OutputInterface $output,
        $templateName,
        $context,
        $verbosity = OutputInterface::VERBOSITY_DEBUG
    ) {
        $output->writeln($this->getView()->render($templateName, $context), $verbosity);
    }

    /**
     * @param OutputInterface $output
     * @param CheckInterface  $checkI
     */
    protected function renderComponentCheckStart(OutputInterface $output, CheckInterface $checkI)
    {
        static $ident;
        if ($ident != $checkI->getIndent()) {
            $ident = $checkI->getIndent();
            $this->renderCheckStart(
                $output,
                lang::translate('TEXT_'.strtoupper($checkI->getCheckComponent()).'_START_CHECK'),
                $checkI->getIndent()
            );
        }
    }

    /**
     * @param ChecksList $checksList
     */
    protected function setChecksList($checksList)
    {
        $this->checksList = $checksList;
    }

    /**
     * @param IncidentManager $incidentManager
     */
    protected function setIncidentManager(IncidentManager $incidentManager)
    {
        $this->incidentManager = $incidentManager;
    }

    /**
     * @param ComponentManager $componentManager
     */
    protected function setComponentManager(ComponentManager $componentManager)
    {
        $this->componentManager = $componentManager;
    }

    /**
     * @param scheduledMaintenance $scheduledMaintenance
     */
    protected function setScheduledMaintenance(ScheduledMaintenance $scheduledMaintenance)
    {
        $this->scheduledMaintenance = $scheduledMaintenance;
    }
}
