<?php

namespace TonicHealthCheck\RemoteCmd;

/**
 * Class CmdAdapterFactory.
 */
class CmdAdapterFactoryException extends \Exception
{
    const TEXT_DOES_NOT_EXIST_ADAPTER_FOR_CLIENT = 'does not exist any cmd adapter for client type:%s';

    /**
     * @param mixed $client
     *
     * @return self
     */
    public static function doesNotExistAdapterForClient($client)
    {
        return new self(sprintf(self::TEXT_DOES_NOT_EXIST_ADAPTER_FOR_CLIENT, get_class($client)));
    }
}
