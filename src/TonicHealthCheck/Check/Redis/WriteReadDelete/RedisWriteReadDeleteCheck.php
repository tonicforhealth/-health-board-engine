<?php

namespace TonicHealthCheck\Check\Redis\WriteReadDelete;

use Predis\Client as PredisClient;
use Predis\PredisException;
use TonicHealthCheck\Check\Redis\AbstractRedisCheck;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisDeleteCheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisWRDCheckException;
use TonicHealthCheck\Check\Redis\WriteReadDelete\Exception\RedisWriteReadCheckException;

/**
 * Class RedisWriteReadDeleteCheck.
 */
class RedisWriteReadDeleteCheck extends AbstractRedisCheck
{
    const CHECK = 'redis-write-read-delete-check';

    /**
     * @var PredisClient
     */
    protected $client;

    /**
     * @param string       $checkNode
     * @param PredisClient $client
     */
    public function __construct($checkNode, PredisClient $client)
    {
        parent::__construct($checkNode);
        $this->setClient($client);
    }

    /**
     * Test redis write&read&delete.
     *
     * @throws RedisDeleteCheckException
     * @throws RedisWRDCheckException
     * @throws RedisWriteReadCheckException
     */
    public function check()
    {
        try {
            $value1 = 'testData_123456';
            $value2 = 'Other_98765';
            $valueKey = 'test_health_check_val';

            $valueOld = $this->getClient()->get($valueKey);
            $this->getClient()->set($valueKey, $value1);

            if ($this->getClient()->get($valueKey) != $value1) {
                throw RedisWriteReadCheckException::doesNotSave($valueOld, $value1, $this->getClient()->get($valueKey));
            }

            $valueOld = $this->getClient()->get($valueKey);
            $this->getClient()->set($valueKey, $value2);

            if ($this->getClient()->get($valueKey) != $value2) {
                throw RedisWriteReadCheckException::doesNotSave($valueOld, $value2, $this->getClient()->get($valueKey));
            }

            $valueOld = $this->getClient()->get($valueKey);
            $this->getClient()->del($valueKey);

            if ($this->getClient()->exists($valueKey)) {
                throw RedisDeleteCheckException::doesNotDelete($valueOld, $this->getClient()->get($valueKey));
            }
        } catch (PredisException $e) {
            throw RedisWRDCheckException::internalProblem($e);
        }
    }

    /**
     * @return PredisClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param PredisClient $client
     */
    protected function setClient(PredisClient $client)
    {
        $this->client = $client;
    }
}
