<?php

namespace TonicHealthCheck\Check\Redis\Connect;

use Predis\Client as PredisClient;
use Predis\PredisException;
use TonicHealthCheck\Check\Redis\AbstractRedisCheck;

/**
 * Class RedisConnectCheck.
 */
class RedisConnectCheck extends AbstractRedisCheck
{
    const CHECK = 'redis-connect-check';

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
     * Check PredisClient can connect to redis server.
     *
     * @throws RedisConnectCheckException
     */
    public function check()
    {
        try {
            $this->getClient()->connect();
        } catch (PredisException $e) {
            throw  RedisConnectCheckException::connectProblem($e);
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
