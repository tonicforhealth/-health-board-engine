<?php

namespace TonicHealthCheck\Check\Http\PerformanceDegradation;

use Http\Client\Common\HttpMethodsClient;
use TonicHealthCheck\Check\Http\AbstractHttpCheck;

/**
 * Class HttpPerformanceDegradationCheck.
 */
class HttpPerformanceDegradationCheck extends AbstractHttpCheck
{
    const CHECK = 'http-performance-degradation-check';

    const AVERAGE_MAX_TIME = 2.0;
    const AVERAGE_TIME_TRY_COUNT = 5;
    const DEFAULT_HTTP_CODE = 200;

    /**
     * @var HttpMethodsClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $defaultUrl = null;

    /**
     * @var float
     */
    protected $averageMaxTime = self::AVERAGE_MAX_TIME;

    /**
     * @var int
     */
    protected $tryCount = self::AVERAGE_TIME_TRY_COUNT;

    /**
     * @param string            $checkNode
     * @param HttpMethodsClient $client
     * @param string            $defaultUrl
     * @param string            $averageMaxTime
     * @param string            $tryCount
     */
    public function __construct($checkNode, HttpMethodsClient $client, $defaultUrl, $averageMaxTime, $tryCount)
    {
        parent::__construct($checkNode);
        $this->setClient($client);
        $this->setDefaultUrl($defaultUrl);
        $this->setAverageMaxTime($averageMaxTime);
        $this->setTryCount($tryCount);
    }

    /**
     * Check Performance Degradation for $url.
     *
     * @param string $url
     * @param float  $averageMaxTime average max time for response time
     * @param int    $tryCount
     *
     * @return bool|void
     *
     * @throws HttpPerformanceDegradationCheckException
     */
    public function check($url = null, $averageMaxTime = null, $tryCount = null)
    {
        if (null === $url) {
            $url = $this->getDefaultUrl();
        }

        if (null === $averageMaxTime) {
            $averageMaxTime = $this->getAverageMaxTime();
        }

        if (null === $tryCount) {
            $tryCount = $this->getTryCount();
        }

        $startTime = microtime(true);
        try {
            $tryCountCounter = $tryCount;
            while (--$tryCountCounter >= 0) {
                $this->getClient()->get($url);
            }
        } catch (\Exception $e) {
            throw new HttpPerformanceDegradationCheckException($e->getMessage(), $e->getCode(), $e);
        }

        $averageTime = (microtime(true) - $startTime) / $tryCount;

        if ($averageTime > $averageMaxTime) {
            throw HttpPerformanceDegradationCheckException::performanceDegradation($averageTime, $averageMaxTime);
        }
    }

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return $this->defaultUrl;
    }

    /**
     * @return float
     */
    public function getAverageMaxTime()
    {
        return $this->averageMaxTime;
    }

    /**
     * @return int
     */
    public function getTryCount()
    {
        return $this->tryCount;
    }

    /**
     * @return HttpMethodsClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $defaultUrl
     */
    protected function setDefaultUrl($defaultUrl)
    {
        $this->defaultUrl = $defaultUrl;
    }

    /**
     * @param float $averageMaxTime
     */
    protected function setAverageMaxTime($averageMaxTime)
    {
        $this->averageMaxTime = $averageMaxTime;
    }

    /**
     * @param int $tryCount
     */
    protected function setTryCount($tryCount)
    {
        $this->tryCount = $tryCount;
    }

    /**
     * @param HttpMethodsClient $client
     */
    protected function setClient(HttpMethodsClient $client)
    {
        $this->client = $client;
    }
}
