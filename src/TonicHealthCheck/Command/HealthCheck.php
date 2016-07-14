<?php

namespace TonicHealthCheck\Command;

use Pimple\Container;
use Symfony\Component\Console\Command\Command;
use diversen\lang;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TonicHealthCheck\Checker\Checker;
use Twig_Environment;

/**
 * Class HealthCheck.
 */
class HealthCheck extends Command
{
    const LOOP_SLEEP_TIME = 30;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Twig_Environment
     */
    protected $view;

    /**
     * @var Checker
     */
    protected $checker;

    /**
     * HealthCheck constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container);

        parent::__construct();
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Checker
     */
    public function getChecker()
    {
        return $this->checker;
    }

    /**
     * @param Checker $checker
     */
    public function setChecker($checker)
    {
        $this->checker = $checker;
    }

    /**
     * @return Twig_Environment
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param mixed $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    protected function initDependency()
    {
        $this->setView($this->getContainer()['twig.env']);
        $this->setChecker($this->getContainer()['checker']);
    }

    protected function configure()
    {
        $this
            ->setName('healthcheck:all')
            ->setDescription('Check health of the all Tonic SB services and components')
            ->addOption(
                'loop',
                null,
                InputOption::VALUE_NONE,
                'If set, checking run in infinity loop'
            )->addOption(
                'loopSleep',
                null,
                InputOption::VALUE_REQUIRED,
                'set loop sleep time sec',
                static::LOOP_SLEEP_TIME
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initDependency();
        $loop = $input->getOption('loop');
        $loopSleep = $input->getOption('loopSleep');
        $sleepF = false;
        do {
            if ($sleepF) {
                sleep($loopSleep);
            }
            $output->writeln(
                $this->getView()->render(
                    'start_end_checker.twig',
                    [
                        'message' => lang::translate('TEXT_CHECK_START'),
                    ]
                )
            );

            $this->getChecker()->performChecks($input, $output);

            $output->writeln(
                $this->getView()->render(
                    'start_end_checker.twig',
                    [
                        'message' => lang::translate('TEXT_CHECK_END'),
                    ]
                )
            );
            $sleepF = true;
        } while ($loop);
    }

    /**
     * @param Container $container
     */
    protected function setContainer($container)
    {
        $this->container = $container;
    }
}
