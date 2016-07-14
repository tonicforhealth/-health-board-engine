<?php

namespace TonicHealthCheck\Maintenance;

use DateTime;
use Http\Client\Common\HttpMethodsClient;

/**
 * Class scheduledMaintenance.
 */
class ScheduledMaintenance
{
    const MAX_SCHEDULE_TIME = 21600;
    const MIN_SCHEDULE_TIME = 60;

    /**
     * @var HttpMethodsClient
     */
    private $cachetRestClient;

    /**
     * @var string
     */
    private $restBaseUrl;

    /**
     * @var int;
     */
    private $maxScheduleTime = self::MAX_SCHEDULE_TIME;

    /**
     * ComponentHandler constructor.
     *
     * @param HttpMethodsClient $cachetRestClient
     * @param string            $restBaseUrl
     */
    public function __construct(
        HttpMethodsClient $cachetRestClient,
        $restBaseUrl,
        $maxScheduleTime = null
    ) {
        $this->setCachetRestClient($cachetRestClient);
        $this->setRestBaseUrl($restBaseUrl);
        if (null !== $maxScheduleTime) {
            $this->maxScheduleTime = $maxScheduleTime;
        }
    }

    /**
     * @return bool
     */
    public function isNowMaintenanceOn()
    {
        $last = $this->getFurthest();

        $isNowMaintenanceOn = false;

        if (isset($last->status) && $last->status == 0
            && empty($last->deleted_at)
            && isset($last->scheduled_at)
            && isset($last->created_at)) {
            $scheduledAt = DateTime::createFromFormat('Y-m-d H:i:s', $last->scheduled_at);
            $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $last->created_at);

            $scheduledInterval = $scheduledAt->getTimestamp() - $createdAt->getTimestamp();

            $scheduledInterval = $scheduledInterval < $this->getMaxScheduleTime() ? $scheduledInterval : $this->getMaxScheduleTime();

            $scheduledTimeEndSec = $createdAt->getTimestamp() + $scheduledInterval - time();

            if ($scheduledTimeEndSec > static::MIN_SCHEDULE_TIME) {
                $isNowMaintenanceOn = true;
            }
        }

        return $isNowMaintenanceOn;
    }

    /**
     * @return HttpMethodsClient
     */
    public function getCachetRestClient()
    {
        return $this->cachetRestClient;
    }

    /**
     * @return string
     */
    public function getRestBaseUrl()
    {
        return $this->restBaseUrl;
    }

    /**
     * @param string $resourcePath
     *
     * @return string
     */
    public function getResourceUrl($resourcePath)
    {
        return $this->getRestBaseUrl().$resourcePath;
    }

    /**
     * @return int
     */
    public function getMaxScheduleTime()
    {
        return $this->maxScheduleTime;
    }

    /**
     * @param HttpMethodsClient $cachetRestClient
     */
    protected function setCachetRestClient($cachetRestClient)
    {
        $this->cachetRestClient = $cachetRestClient;
    }

    /**
     * @param string $restBaseUrl
     */
    protected function setRestBaseUrl($restBaseUrl)
    {
        $this->restBaseUrl = $restBaseUrl;
    }

    /**
     * @param int $maxScheduleTime
     */
    protected function setMaxScheduleTime($maxScheduleTime)
    {
        $this->maxScheduleTime = $maxScheduleTime;
    }

    /**
     * @return array
     *
     * @todo refactor it after it will be done:https://github.com/CachetHQ/Cachet/pull/1579
     */
    private function getFurthest()
    {
        $response = $this->getCachetRestClient()->get(
            $this->getResourceUrl('/incidents/?sort=scheduled_at&order=desc')
        );

        $componentData = json_decode($response->getBody()->getContents());
        $last = false;

        if (isset($componentData->data[0])) {
            $last = $componentData->data[0];
        }

        return $last;
    }
}
