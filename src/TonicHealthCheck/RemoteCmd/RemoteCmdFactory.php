<?php

namespace TonicHealthCheck\RemoteCmd;

use Docker\Docker;
use Docker\Http\DockerClient;
use Ssh\Authentication\PublicKeyFile;
use Ssh\Configuration as SshConfiguration;
use Ssh\Session;

/**
 * Proxy Class RemoteCmdFactory.
 */
class RemoteCmdFactory
{
    /**
     * @param array $parameters
     *
     * @return null|RemoteCmd
     *
     * @throws CmdAdapterFactoryException
     */
    public static function build($parameters)
    {
        $remoteCmd = null;
        $cmdType = $parameters['type'];
        if ($cmdType == CmdAdapterFactory::CMD_TYPE_DOCKER) {
            $containerId = $parameters['container_id'];
            $containerDockerHost = $parameters['docker_host'];
            $containerDockerCertPath = isset($parameters['docker_cecrt_path']) ? $parameters['docker_cecrt_path'] : null;
            $containerDockerUseTls = isset($parameters['docker_use_tls']) ? $parameters['docker_use_tls'] : false;
            $glusterFSConnectTimeout = isset($parameters['connect_timeout']) ? $parameters['connect_timeout'] : false;
            $dockerClient = new DockerClient(
                [
                    'defaults' => [
                        'timeout' => $glusterFSConnectTimeout,
                    ],
                ],
                $containerDockerHost,
                static::createStreamContext($containerDockerCertPath),
                $containerDockerUseTls
            );
            $docker = new Docker($dockerClient);
            $remoteCmd = new RemoteCmd(CmdAdapterFactory::createAdapterForTransport($docker, $containerId));
        } elseif ($cmdType == CmdAdapterFactory::CMD_TYPE_SSH) {
            $sshHost = $parameters['ssh_host'];
            $sshPort = $parameters['ssh_port'];

            $sshUserName = $parameters['ssh_auth']['username'];
            $sshPrivateKeyFile = $parameters['ssh_auth']['private_key_path'];
            $sshPublicKeyFile = $parameters['ssh_auth']['public_key_path'];
            $sshPassPhrase = isset($parameters['ssh_auth']['pass_phrase']) ? $parameters['ssh_auth']['pass_phrase'] : null;

            $configuration = new SshConfiguration($sshHost, $sshPort);
            $authentication = new PublicKeyFile($sshUserName, $sshPublicKeyFile, $sshPrivateKeyFile, $sshPassPhrase);

            $sshSession = new Session($configuration, $authentication);

            $remoteCmd = new RemoteCmd(CmdAdapterFactory::createAdapterForTransport($sshSession));
        }

        return $remoteCmd;
    }

    protected static function createStreamContext($dockerCertPath = null)
    {
        $dockerCertPath = null === $dockerCertPath ? getenv('DOCKER_CERT_PATH') : $dockerCertPath;
        $context = null;

        if (!empty($dockerCertPath) && is_dir($dockerCertPath)) {
            $cafile = $dockerCertPath.DIRECTORY_SEPARATOR.'ca.pem';
            $certfile = $dockerCertPath.DIRECTORY_SEPARATOR.'cert.pem';
            $keyfile = $dockerCertPath.DIRECTORY_SEPARATOR.'key.pem';
            $peername = $dockerCertPath ? getenv('DOCKER_PEER_NAME') : 'boot2docker';
            $fullcert = tempnam(sys_get_temp_dir(), 'docker-certfile');

            file_put_contents($fullcert, file_get_contents($certfile));
            file_put_contents($fullcert, file_get_contents($keyfile), FILE_APPEND);

            $context = stream_context_create(
                [
                    'ssl' => [
                        'cafile' => $cafile,
                        'local_cert' => $fullcert,
                        'peer_name' => $peername,
                    ],
                ]
            );
        }

        return $context;
    }
}
