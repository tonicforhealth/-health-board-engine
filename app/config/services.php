<?php

use Cron\CronExpression;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use diversen\lang;
use Doctrine\Common\EventManager;
use Http\Adapter\Guzzle5\Client as Guzzle5Adapter;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Plugin\AuthenticationPlugin;
use Http\Client\Plugin\PluginClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\Authentication\BasicAuth;
use PhpImap\Mailbox;
use Pimple\Container;
use Saxulum\DoctrineOrmManagerRegistry\Provider\DoctrineOrmManagerRegistryProvider;
use Stomp\StatefulStomp;
use Supervisor\Connector\XmlRpc;
use Supervisor\Supervisor;
use TonicForHealth\PagerDutyClient\Client\EventClientFactory;
use TonicHealthCheck\CachetHQ\Authentication\Token;
use TonicHealthCheck\Check\ActiveMQ\ActiveMQCheckCollection;
use TonicHealthCheck\Check\ActiveMQ\Connect\ActiveMQConnectCheck;
use TonicHealthCheck\Check\ActiveMQ\SendReciveAck\ActiveMQSendReciveAckCheck;
use TonicHealthCheck\Check\CheckInterface;
use TonicHealthCheck\Check\DB\BaseTableExist\DBBaseTableExistCheck;
use TonicHealthCheck\Check\DB\Connect\DBConnectCheck;
use TonicHealthCheck\Check\DB\DBCheckCollection;
use TonicHealthCheck\Check\DB\PDOFactory;
use TonicHealthCheck\Check\Elasticsearch\ElasticsearchCheckCollection;
use TonicHealthCheck\Check\Elasticsearch\GetDocument\ElasticsearchGetDocumentCheck;
use TonicHealthCheck\Check\Email\EmailCheckCollection;
use TonicHealthCheck\Check\Email\Persist\PersistCollectionToFile;
use TonicHealthCheck\Check\Email\Receive\EmailReceiveCheck;
use TonicHealthCheck\Check\Email\Send\EmailSendCheck;
use TonicHealthCheck\Check\MountFS\Mount\MountFSMountCheck;
use TonicHealthCheck\Check\MountFS\MountFSCheckCollection;
use TonicHealthCheck\Check\MountFS\WriteReadDelete\MountFSWriteReadDeleteCheck;
use TonicHealthCheck\Check\Http\HttpCheckCollection;
use TonicHealthCheck\Check\Http\Code\HttpCodeCheck;
use TonicHealthCheck\Check\Http\PerformanceDegradation\HttpPerformanceDegradationCheck;
use TonicHealthCheck\Check\Processing\MinAmountWorkers\ProcessingMinAmountWorkersCheck;
use TonicHealthCheck\Check\Processing\ProcessingCheckCollection;
use TonicHealthCheck\Check\Processing\StatusWorkers\ProcessingStatusWorkersCheck;
use TonicHealthCheck\Check\Redis\Connect\RedisConnectCheck;
use TonicHealthCheck\Check\Redis\RedisCheckCollection;
use TonicHealthCheck\Check\Redis\WriteReadDelete\RedisWriteReadDeleteCheck;
use TonicHealthCheck\Checker\Checker;
use TonicHealthCheck\Checker\ChecksList;
use TonicHealthCheck\Component\ComponentManager;
use TonicHealthCheck\Incident\ChecksIncidentTypeMapper;
use TonicHealthCheck\Incident\IncidentEventSubscriber;
use TonicHealthCheck\Incident\IncidentManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use Elasticsearch\ClientBuilder as ElasticsearchClientBuilder;
use Predis\Client as PredisClient;
use Stomp\Client as StompClient;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentType\IncidentTypeOptions;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentTypeResolver;
use TonicHealthCheck\Incident\IncidentTypeResolver\IncidentTypeResolverInterface;
use TonicHealthCheck\Incident\Siren\IncidentSiren;
use TonicHealthCheck\Incident\Siren\IncidentSirenCollection;
use TonicHealthCheck\Incident\Siren\NotificationType\EmailNotificationType;
use TonicHealthCheck\Incident\Siren\NotificationType\FileNotificationType;
use TonicHealthCheck\Incident\Siren\NotificationType\PagerDutyNotificationType;
use TonicHealthCheck\Incident\Siren\NotificationType\RequestNotificationType;
use TonicHealthCheck\Incident\Siren\Subject\Subject;
use TonicHealthCheck\Incident\Siren\Subject\SubjectCollection;
use TonicHealthCheck\Maintenance\ScheduledMaintenance;
use TonicHealthCheck\RemoteCmd\RemoteCmdFactory;
use fXmlRpc\Transport\HttpAdapterTransport;
use Ivory\HttpAdapter\HttpAdapterFactory;
use Ivory\HttpAdapter\Configuration as HAConfiguration;
use fXmlRpc\Client as fXmlRpcClient;

/* @var \Pimple\Container $container */

return function ($container) {
    $config = $container['config'];
    $container['lang.dirs'] = $config['lang']['dirs'];
    $container['lang.language'] = $config['lang']['language'];

    $container['lang'] = function (Container $c) {
        $lang = new lang();
        foreach ($c['lang.dirs'] as $dir) {
            $lang->setDirsInsideDir($dir);
        }

        $lang->loadLanguage($c['lang.language']);

        return $lang;
    };

    $container['twig.dirs'] = $config['twig']['dirs'];

    $container['twig.loader'] = function (Container $c) {
        return new Twig_Loader_Filesystem($c['twig.dirs']);
    };

    $container['twig.env'] = function (Container $c) {
        return new Twig_Environment($c['twig.loader']);
    };

//$container['doctrine.em.conf'] = $config['doctrine']['conf'];
//$container['doctrine.em.metadata_drivers'] = $config['doctrine']['metadata_drivers'];

//$container['doctrine.em'] = function (Container $c) {
//    $annotationMetadataDrivers = array_filter($c['doctrine.em.metadata_drivers'], function ($var) {
//        return $var['type'] == 'annotation';
//    });
//    $annotationPath = [];
//    array_map(function ($var) use (&$annotationPath) {
//        $annotationPath = array_merge($annotationPath, (array) $var['paths']);
//    }, $annotationMetadataDrivers);
//
//    $config = Setup::createAnnotationMetadataConfiguration($annotationPath);
//    $entityManager = EntityManager::create($c['doctrine.em.conf'], $config);
//
//    return $entityManager;
//};

    $container['dbs'] = $config['doctrine']['dbs'];

    $container['dbs.event_manager.default'] = function (Container $c) {
        return new EventManager();
    };

    $container['dbs.event_manager'] = function (container $c) {
        $eventManagers = [];

        foreach ($c['dbs'] as $key => $dbConfig) {
            $eventManagers[$key] = $c['dbs.event_manager.default'];
        }

        return $eventManagers;
    };

    $container->register(
        new DoctrineOrmServiceProvider(),
        $config['doctrine']['orm']
    );

    $container['doctrine.em'] = function (Container $c) {
        return $c['doctrine']->getManager();
    };


    $container['rest.cachet.config'] = $config['rest']['cachet']['config'];
    $container['rest.cachet.base_url'] = $config['rest']['cachet']['base_url'];

    $container['rest.cachet.client.adapter'] = function (Container $c) {
        $config = $c['rest.cachet.config'];
        $plugins = [];
        if (isset($config['username']) && isset($config['password'])) {
            $authentication = new BasicAuth($config['username'], $config['password']);
            $plugins[] = new AuthenticationPlugin($authentication);
        } elseif (isset($config['token'])) {
            $authentication = new Token($config['token']);
            $plugins[] = new AuthenticationPlugin($authentication);
        }

        return new PluginClient(
            HttpClientDiscovery::find(),
            $plugins
        );
    };

    $container['rest.cachet.client'] = function (Container $c) {
        return new HttpMethodsClient(
            $c['rest.cachet.client.adapter'],
            MessageFactoryDiscovery::find()
        );
    };
    $container['incident.siren.notifications'] = $config['incident']['siren']['notifications'];
    $container['incident.siren'] = function (Container $c) {

        $incidentSirenCollection = new IncidentSirenCollection();
        foreach ($c['incident.siren.notifications'] as $notificationConfig) {

            $notificationsType = null;
            if ($notificationConfig['type'] == 'file') {
                $notificationsType = new FileNotificationType(
                    $notificationConfig['message_path']
                );
            } elseif ($notificationConfig['type'] == 'pager_duty') {
                $notificationsType = new PagerDutyNotificationType(
                    EventClientFactory::createEventClient($notificationConfig['api_root_url']),
                    $notificationConfig['service_key']
                );

            } elseif ($notificationConfig['type'] == 'email') {
                $emailSecurity = isset($notificationConfig['transport']['security'])?$notificationConfig['transport']['security']:null;
                $transport = Swift_SmtpTransport::newInstance($notificationConfig['transport']['server'], $notificationConfig['transport']['port'], $emailSecurity);

                if (isset($notificationConfig['transport']['username'])) {
                    $transport->setUsername($notificationConfig['transport']['username']);

                }

                if (isset($notificationConfig['transport']['password'])) {
                    $transport->setPassword($notificationConfig['transport']['password']);

                }

                $mailer = Swift_Mailer::newInstance($transport);

                $notificationsType = new EmailNotificationType(
                    $mailer,
                    $notificationConfig['from'],
                    $notificationConfig['from_name']
                );
            } elseif ($notificationConfig['type'] == 'request') {
                $notificationsType = new RequestNotificationType(
                    $c['rest.cachet.client'],
                    $notificationConfig['resource_url']
                );
            }

            if (null != $notificationsType) {

                $subjectCollection = new SubjectCollection();
                foreach ($notificationConfig['subjects'] as $subjectConfig) {

                    $target = $subjectConfig['target'];
                    $schedule = isset($subjectConfig['schedule'])?CronExpression::factory($subjectConfig['schedule']):null;

                    $subjectCollection->add(new Subject($target, $schedule));
                }

                $incidentSirenCollection->add(
                    new IncidentSiren(
                        $notificationsType,
                        $subjectCollection
                    )
                );
            }
        }

        return $incidentSirenCollection;
    };

    $container['incident.eventsubscriber'] = function (Container $c) {
        $incidentEventSubscriber =  new IncidentEventSubscriber(
            $c['incident.siren']
        );

        return $incidentEventSubscriber;
    };

    $container->register(new DoctrineOrmManagerRegistryProvider());

    $container['component.manager'] = function (Container $c) {
        $incidentHandler = new ComponentManager(
            $c['doctrine.em'],
            $c['rest.cachet.client'],
            $c['rest.cachet.base_url']
        );

        return $incidentHandler;
    };

    $container['incident.checks_type_resolver'] = function (Container $c) {
        $checksIncidentTypeMapper = new IncidentTypeResolver(
            $c['doctrine.em']
        );
        return $checksIncidentTypeMapper;
    };

    $container['incident.manager'] = function (Container $c) {
        $incidentHandler = new IncidentManager(
            $c['doctrine.em'],
            $c['incident.checks_type_resolver']
        );

        $c['dbs.event_manager.default']->addEventSubscriber($c['incident.eventsubscriber']);

        return $incidentHandler;
    };

    $container['scheduled_maintenance'] = function (Container $c) {
        $incidentHandler = new ScheduledMaintenance(
            $c['rest.cachet.client'],
            $c['rest.cachet.base_url']
        );

        return $incidentHandler;
    };

    $container['guzzle_http_client_callback'] = function (Container $c) {
        return function ($httpConfig) {
            return $guzzleHttpClient = new GuzzleHttpClient(
                $httpConfig
        );};
    };

    $container['checker.http'] = $config['http'];

    $container['checker.http_check'] = function (Container $c) {
        $processingCheckCollection = new HttpCheckCollection();

        foreach ($c['checker.http'] as $checkNode => $nodeConfig) {
            $guzzleHttpClientCallback = $c['guzzle_http_client_callback'];
            $guzzleHttpClient = $c['guzzle_http_client_callback'](
                $nodeConfig['http_config']
            );
            $client = new HttpMethodsClient(
                new Guzzle5Adapter($guzzleHttpClient),
                MessageFactoryDiscovery::find()
            );
            $processingCheckCollection->add(
                new HttpCodeCheck(
                    $checkNode,
                    $client,
                    $nodeConfig['url']
                )
            );

            $processingCheckCollection->add(
                new HttpPerformanceDegradationCheck(
                    $checkNode,
                    $client,
                    $nodeConfig['url'],
                    $nodeConfig['average_max_time'],
                    $nodeConfig['try_count']
                )
            );
        }

        return $processingCheckCollection;
    };

    $container['checker.db'] = $config['db'];

    $container['checker.db_check'] = function (Container $c) {
        $dBCheckCollection = new DBCheckCollection();

        foreach ($c['checker.db'] as $checkNode => $nodeConfig) {
            $PDOFactory = new PDOFactory(
                $nodeConfig['dsn'],
                $nodeConfig['user'],
                $nodeConfig['password']
            );

            $dBConnectCheck = new DBConnectCheck(
                $checkNode,
                $PDOFactory
            );
            $dBCheckCollection->add($dBConnectCheck);

            $dBCheckCollection->add(
                new DBBaseTableExistCheck(
                    $checkNode,
                    $PDOFactory,
                    $nodeConfig['tables_exist_check_list']
                )
            );
        }

        return $dBCheckCollection;
    };

    $container['checker.elasticsearch'] = $config['elasticsearch'];

    $container['checker.elasticsearch_check'] = function (Container $c) {
        $elasticsearchCheckCollection = new ElasticsearchCheckCollection();

        foreach ($c['checker.elasticsearch'] as $checkNode => $nodeConfig) {
            $sslVerification = isset($nodeConfig['ssl_verification']) ? $nodeConfig['ssl_verification'] : false;
            $elasticsearchClient = ElasticsearchClientBuilder::create()
                ->setHosts($nodeConfig['hosts'])
                ->setSSLVerification($sslVerification)
                ->build();

            $elasticsearchCheckCollection->add(
                new ElasticsearchGetDocumentCheck(
                    $checkNode,
                    $elasticsearchClient,
                    $nodeConfig['index'],
                    $nodeConfig['type'],
                    $nodeConfig['min_size']
                )
            );
        }

        return $elasticsearchCheckCollection;
    };

    $container['checker.redis'] = $config['redis'];

    $container['checker.redis_check'] = function (Container $c) {
        $redisCheckCollection = new RedisCheckCollection();

        foreach ($c['checker.redis'] as $checkNode => $nodeConfig) {
            $predisClient = new PredisClient(
                [
                    'scheme' => $nodeConfig['scheme'],
                    'host' => $nodeConfig['host'],
                    'port' => $nodeConfig['port'],
                ]
            );

            $redisCheckCollection->add(
                new RedisConnectCheck(
                    $checkNode,
                    $predisClient
                )
            );
            $redisCheckCollection->add(
                new RedisWriteReadDeleteCheck(
                    $checkNode,
                    $predisClient
                )
            );
        }

        return $redisCheckCollection;
    };

    $container['checker.activemq'] = $config['stomp'];

    $container['checker.activemq_check'] = function (Container $c) {
        $activeMQCheckCollection = new ActiveMQCheckCollection();

        foreach ($c['checker.activemq'] as $checkNode => $nodeConfig) {
            $clientStomp = new StompClient(
                $nodeConfig['broker']
            );

            $clientStomp->setLogin(
                $nodeConfig['user'],
                $nodeConfig['password']
            );

            $statefulStomp = new StatefulStomp($clientStomp);

            $destination = isset($nodeConfig['destination']) ? $nodeConfig['destination'] : null;

            $body = null;
            if (isset($nodeConfig['body']) && null !== $nodeConfig['body']) {
                $body = $nodeConfig['body'];
            } elseif (isset($nodeConfig['body_file']) && is_readable($nodeConfig['body_file'])) {
                $body = file_get_contents($nodeConfig['body_file']);
            }

            $timeOut = isset($nodeConfig['time_out']) ? $nodeConfig['time_out'] : null;

            $activeMQCheckCollection->add(
                new ActiveMQConnectCheck(
                    $checkNode,
                    $statefulStomp
                )
            );
            $activeMQCheckCollection->add(
                new ActiveMQSendReciveAckCheck(
                    $checkNode,
                    $statefulStomp,
                    $destination,
                    $body,
                    $timeOut
                )
            );
        }

        return $activeMQCheckCollection;
    };

    $container['checker.mountfs'] = $config['mountFS'];

    $container['checker.mountfs_check'] = function (Container $c) {
        $glusterFSCheckCollection = new MountFSCheckCollection();
        foreach ($c['checker.mountfs'] as $checkNode => $nodeConfig) {
            $remoteCmd = RemoteCmdFactory::build($nodeConfig);

            $fstabFile = isset($nodeConfig['fstab_file']) ? $nodeConfig['fstab_file'] : null;
            $mountName = isset($nodeConfig['mount_name']) ? $nodeConfig['mount_name'] : null;
            $currentMountListFile = isset($nodeConfig['current_mount_list_file']) ? $nodeConfig['current_mount_list_file'] : null;

            $glusterFSCheckCollection->add(
                new MountFSMountCheck(
                    $checkNode,
                    $remoteCmd,
                    $mountName,
                    $fstabFile,
                    $currentMountListFile
                )
            );

            $glusterFSCheckCollection->add(
                new MountFSWriteReadDeleteCheck(
                    $checkNode,
                    $remoteCmd,
                    $mountName,
                    $fstabFile,
                    $currentMountListFile
                )
            );
        }

        return $glusterFSCheckCollection;
    };

    $container['checker.processing'] = $config['processing'];

    $container['checker.processing_check'] = function (Container $c) {
        $processingCheckCollection = new ProcessingCheckCollection();

        foreach ($c['checker.processing'] as $checkNode => $nodeConfig) {
            $supervisorUrl = $nodeConfig['url'];
            $supervisorMinAmountWorkers = $nodeConfig['min_amount_workers'];
            $workersGroupMaskRegex = $nodeConfig['workers_group_mask_regex'];
            $maxFailTime = $nodeConfig['max_fail_time'];

            $clientRpcConfig = new HAConfiguration();

            $guzzleHttpAdapter = HttpAdapterFactory::guess();

            $guzzleHttpAdapter->setConfiguration($clientRpcConfig);

            $httpAdapterTransport = new HttpAdapterTransport($guzzleHttpAdapter);

            $xmlRpcClient = new fXmlRpcClient($supervisorUrl, $httpAdapterTransport);

            $xmlRpcConnector = new XmlRpc($xmlRpcClient);

            $supervisor = new Supervisor($xmlRpcConnector);

            $processingCheckCollection->add(
                new ProcessingMinAmountWorkersCheck(
                    $checkNode,
                    $supervisor,
                    $supervisorMinAmountWorkers,
                    $workersGroupMaskRegex
                )
            );

            $processingCheckCollection->add(
                new ProcessingStatusWorkersCheck(
                    $checkNode,
                    $c['doctrine.em'],
                    $supervisor,
                    $workersGroupMaskRegex,
                    $maxFailTime
                )
            );
        }

        return $processingCheckCollection;
    };

    $container['checker.email'] = $config['email'];

    $container['checker.email_check.persist_collection'] = function (Container $c) {
        $persistCollection = new PersistCollectionToFile();

        return $persistCollection;
    };

    $container['checker.email_check'] = function (Container $c) {
        $emailCheckCollection = new EmailCheckCollection();

        foreach ($c['checker.email'] as $checkNode => $nodeConfig) {
            $emailSecurity = isset($nodeConfig['transport']['security']) ? $nodeConfig['transport']['security'] : null;
            $transport = Swift_SmtpTransport::newInstance(
                $nodeConfig['transport']['server'],
                $nodeConfig['transport']['port'],
                $emailSecurity
            );

            if (isset($nodeConfig['transport']['username'])) {
                $transport->setUsername($nodeConfig['transport']['username']);
            }

            if (isset($nodeConfig['transport']['password'])) {
                $transport->setPassword($nodeConfig['transport']['password']);
            }

            $mailer = Swift_Mailer::newInstance($transport);

            $mailbox = new Mailbox(
                $nodeConfig['inbox']['imap_path'],
                $nodeConfig['inbox']['username'],
                $nodeConfig['inbox']['password']
            );

            $emailCheckCollection->add(
                new EmailSendCheck(
                    $checkNode,
                    $mailer,
                    $c['checker.email_check.persist_collection'],
                    $nodeConfig['from'],
                    $nodeConfig['inbox']['email'],
                    $nodeConfig['send_interval']
                )
            );

            $emailCheckCollection->add(
                new EmailReceiveCheck(
                    $checkNode,
                    $mailbox,
                    $c['checker.email_check.persist_collection'],
                    $nodeConfig['receive_max_time']
                )
            );
        }

        return $emailCheckCollection;
    };

    $container['checker.checks_list_flags'] = $config['checks_list'];

    $container['checker.checks_list_link'] = function (Container $c) {
        return $checksList = new ChecksList();
    };

    $container['checker.checks_list'] = function (Container $c) {
        $checksList = $c['checker.checks_list_link'];
        /** @var IncidentTypeResolverInterface $checksIncidentTypeResolver */
        $checksIncidentTypeResolver = $c['incident.checks_type_resolver'];
        foreach ($c['checker.checks_list_flags'] as $ident => $flag) {
            if (false === $flag) {
                continue;
            }
            if (isset($c['checker.'.$ident])) {
                $checkContainer = $c['checker.'.$ident];
                if (is_array($flag)) {
                    foreach ($flag as $checkNode => $nodeFlag) {
                        if (false === $nodeFlag) {
                            continue;
                        }
                        /** @var CheckInterface $nodeChecks */
                        foreach ($checkContainer as $nodeChecks) {
                            if ($nodeChecks->getCheckNode() === $checkNode) {
                                if (is_array($nodeFlag)) {
                                    foreach ($nodeFlag as $checkName => $checkFlag) {
                                        if (false === $checkFlag || (is_array(
                                                    $checkFlag
                                                ) && isset($checkFlag['enabled']) && $checkFlag['enabled'] === false)
                                        ) {
                                            continue;
                                        }
                                        if ($nodeChecks->getCheckIdent() === $checkName) {
                                            $checksList->add($nodeChecks);


                                            if (is_array($checkFlag) && isset($checkFlag['incident_options'])) {
                                                $checkOptions = $checkFlag['incident_options'];

                                                $incidentTypeOptions = new IncidentTypeOptions($nodeChecks->getIndent(), $checkOptions['type']);

                                                if(isset($checkOptions['occurrence'])&& isset($checkOptions['occurrence_period'])){
                                                    $incidentTypeOptions->setOccurrence($checkOptions['occurrence']);
                                                    $incidentTypeOptions->setOccurrencePeriod($checkOptions['occurrence_period']);
                                                    if(isset($checkOptions['occurrence_info_type'])){
                                                        $incidentTypeOptions->setInfoTypeToFire($checkOptions['occurrence_info_type']);
                                                    }
                                                }

                                                $checksIncidentTypeResolver->registerChecksIncidentTypeOptions($incidentTypeOptions);
                                            }
                                        }
                                    }
                                } else {
                                    $checksList->add($nodeChecks);
                                }
                            }
                        }
                    }
                } else {
                    $checksList->add($checkContainer);
                }
            }
        }

        return $checksList;
    };

    $container['checker'] = function (Container $c) {
        return new Checker(
            $c['checker.checks_list'],
            $c['component.manager'],
            $c['incident.manager'],
            $c['scheduled_maintenance'],
            $c['twig.env']
        );
    };
};
