<?php

namespace TonicHealthCheck\RemoteCmd;

/**
 * Class CmdAdapterFactory.
 */
class CmdAdapterFactory
{
    const CMD_TYPE_DOCKER = 'docker';

    const CMD_TYPE_SSH = 'ssh';

    /** @var array */
    private static $adapters = [
        self::CMD_TYPE_DOCKER => [
            'adapter' => '\TonicHealthCheck\RemoteCmd\CmdAdapter\DockerCmdAdapter',
            'client' => '\Docker\Docker',
        ],
        self::CMD_TYPE_SSH => [
            'adapter' => '\TonicHealthCheck\RemoteCmd\CmdAdapter\SshCmdAdapter',
            'client' => '\Ssh\Session',
        ],
    ];

    /**
     * Creates an http adapter.
     *
     * @param string       $client           The name.
     * @param array|string $argsForConstruct
     *
     * @return CmdAdapterInterface If the http adapter does not exist or is not usable.
     *
     * @throws CmdAdapterFactoryException If the http adapter does not exist or is not usable.
     */
    public static function createAdapterForTransport($client, $argsForConstruct = [])
    {
        $adapterFound = null;
        foreach (self::$adapters as $adapterTypeItem) {
            if (is_a($client, $adapterTypeItem['client'])) {
                $adapterFound = $adapterTypeItem;
                break;
            }
        }

        if (null === $adapterFound) {
            throw CmdAdapterFactoryException::doesNotExistAdapterForClient($client);
        }
        $adapterClassName = $adapterFound['adapter'];
        $r = new \ReflectionClass($adapterClassName);
        $argsForConstruct = (array) $argsForConstruct;
        array_unshift($argsForConstruct, $client);

        return $r->newInstanceArgs($argsForConstruct);
    }
}
