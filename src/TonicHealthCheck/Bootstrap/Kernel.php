<?php

namespace TonicHealthCheck\Bootstrap;

use Pimple\Container;

/**
 * Class Kernel.
 */
class Kernel
{
    const CONFIG_DEFAULT_PATH = __DIR__.'/../../../app/config/';
    const CONFIG_FILE_TEMPLATE = '%sconfig%s.php';
    const PARAMETER_DEFAULT_FILE_TEMPLATE = '%sparameter%s.php';
    const SERVICES_DEFAULT_PATH = 'services.php';
    const ENV_PROD = 'prod';
    const ENV_DEV = 'dev';
    const ENV_TEST = 'test';

    /**
     * @var string
     */
    private $environment;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Container
     */
    private $container;

    /**
     * Kernel constructor.
     *
     * @param string $env
     */
    public function __construct($env)
    {
        $this->environment = $env;
    }

    /**
     * Boot kernel.
     */
    public function boot()
    {
        $this->initializeConfig();

        $this->initializeContainer();
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Initialize container.
     */
    protected function initializeContainer()
    {
        $this->setContainer(new \Pimple\Container());
        $this->getContainer()['config'] = $this->getConfig();

        $servicesF = require self::CONFIG_DEFAULT_PATH.self::SERVICES_DEFAULT_PATH;
        $servicesF($this->getContainer());
    }

    /**
     * Initialize config.
     */
    protected function initializeConfig()
    {
        $configFile = sprintf(self::CONFIG_FILE_TEMPLATE, self::CONFIG_DEFAULT_PATH, $this->getConfigPrefix());

        $this->setConfig(require $configFile);

        $parameterFile = sprintf(self::PARAMETER_DEFAULT_FILE_TEMPLATE, self::CONFIG_DEFAULT_PATH, $this->getConfigPrefix());
        if (is_readable($parameterFile)) {
            $parameter = require $parameterFile;
            $this->setConfig(array_replace_recursive($this->getConfig(), $parameter));
        }
    }

    /**
     * @param string $environment
     */
    protected function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param array $config
     */
    protected function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param Container $container
     */
    protected function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getConfigPrefix()
    {
        $prefix = '';
        if (self::ENV_PROD !== $this->getEnvironment()) {
            $prefix = '.'.strtolower($this->getEnvironment());
        }

        return $prefix;
    }
}
