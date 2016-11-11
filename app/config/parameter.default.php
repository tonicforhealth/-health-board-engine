<?php

return [
    'rest' => [
        'cachet' => [
            'base_url' => 'http://localhost:8000/api/v1',
            'config' => [
                //'username' => 'test@gmail.com',
                //'password' => 'xxxxxx',
            ],
        ],
    ],
    'incident' => [
        'siren' => [
            'notifications' => [
//                'file' => [
//                    'type' => 'file',
//                    'message_path' => '/tmp',
//                    'subjects' => [
//                        'tel1' => [
//                            'target'=> 'someCreateFileName',
//                        ],
//                    ],
//                ],
                'email' => [
                    'type' => 'email',
                    'transport' =>[
                        'server'   => 'smtp.mailgun.org',
                        'port'     => 465,
                        'security' => 'ssl',
                        'username' => 'test@gmail.com',
                        'password' => 'xxxxxx',
                    ],
                    'from'      => 'test@gmail.com',
                    'from_name' => 'Health Check',
                    'subjects' => [
                        'email_test' => [
                            'target'   => 'test@gmail.com',
                            'schedule' => '* * 0,1,2,3,5,7,9,11,13,15,17,19,21,23,25,27,29,30,31 * * *',
                        ],
                    ],
                ],
//                'pager_duty' => [
//                    'type' => 'pager_duty',
//                    'service_key' => 'a1ccfb3646a844a2sc3d1fc23582cf94',
//                    'api_root_url' => 'https://events.pagerduty.com/generic/2010-04-15',
//                    'subjects' => [
//                        'drefixs' => [
//                            'target' => 'test@gmail.com',
//                        ],
//                    ],
//                ],
//                'cached' => [
//                    'type' => 'request',
//                    'resource_url' => '/incidents',
//                    'subjects' => [
//                        'cached' => [
//                            'target' => 'http://localhist:8000/api/v1',
//                        ],
//                    ],
//                ],
            ],
        ],
    ],
    'http' => [
        'local' => [
            'url' => 'http://localhost/builder/login',
            'average_max_time' => 2.0,
            'try_count' => 5,
            'http_config' => [
                'defaults' => [
                    'verify' => false,
                    'connect_timeout' => 10,
                    'timeout' => 10,
                ],
            ],
        ],
    ],
    'db' => [
        'local' => [
            'dsn' => 'mysql:host=localhost;port=3306;dbname=dbname',
            'user' => 'test',
            'password' => 'xxxxxx',
            'tables_exist_check_list' => ['patient_profile', 'survey'],
        ],
    ],
    'elasticsearch' => [
        'local' => [
            'hosts' => [
                0 => 'http://localhost:9200',
            ],
            'index' => 'report_data',
            'type' => 'survey',
            'min_size' => 5,
        ],
    ],
    'redis' => [
        'local' => [
            'scheme' => 'tcp',
            'host' => 'localhost',
            'port' => 6379,
        ],
    ],
    'stomp' => [
        'localhost' => [
            'broker' => 'udp://localhost:61620',
            'user' => 'admin',
            'password' => 'xxxxxx',
        ],
    ],
    'mountFS' => [
        'local_web_docker' => [
            'type' => 'docker',
            'container_id' => 'docker_container_id_or_name',
            'docker_host' => 'unix://var/run/docker.sock',
            //'docker_cecrt_path' => getenv("HOME").'/.docker/machine/machines/default',
            'docker_use_tls' => false,
        ],
        'local_ssh' => [
            'mount_name' => 'smbfs',
            'fstab_file' => getenv('HOME').'/test/mountfs/fstab',
            'current_mount_list_file' => getenv('HOME').'/test/mountfs/mount',
            'type' => 'ssh',
            'ssh_host' => 'localhost',
            'ssh_port' => 22,
            'ssh_auth' => [
                'username' => 'testUser',
                'private_key_path' => getenv('HOME').'/.ssh/id_rsa',
                'public_key_path' => getenv('HOME').'/.ssh/id_rsa.pub',
                'pass_phrase' => 'test_pass_phrase',
            ],
        ],
    ],
    'processing' => [
        'local' => [
            'min_amount_workers' => 1,
            'workers_group_mask_regex' => '/worker/',
            'max_fail_time' => 30,
            'url' => 'http://localhost:9001/RPC2',
        ],
    ],
    'email' => [
        'local' => [
            'transport' => [
                'type' => 'smtp',
                'server' => 'smtp.gmail.com',
                'port' => 2526,
                'username' => 'healthcheck@gmail.com',
                'password' => 'xxxxxx',
                'security' => null,
            ],
            'inbox' => [
                'type' => 'imap',
                'email' => 'checkemail@gmail.com',
                'imap_path' => '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX',
                'username' => 'checkemail@gmail.com',
                'password' => 'xxxxxx',
            ],
            'from' => 'healthcheck@gmail.com',
            'receive_max_time' => 10,
            'send_interval' => 10,
        ],
    ],
    'checks_list' => [
        'http_check' => [
            'local' => [
                'http-code-check' => [
                    'incident_options' => [
                        'type' => 'urgent',
                        'occurrence' => 3,
                        'occurrence_period' => 100,
                        'occurrence_info_type' => 'minor'
                    ],
                ],
                'http-performance-degradation-check' => true,
            ],
        ],
        'db_check' => [
            'local' => [
                'db-connect-check' => true,
                'db-base-table-exist-check' => true,
            ],
        ],
        'elasticsearch_check' => [
            'local' => [
                'elasticsearch-get-document-check' => true,
            ],
        ],
        'redis_check' => [
            'local' => [
                'redis-connect-check' => true,
                'redis-write-read-delete-check' => true,
            ],
        ],
        'activemq_check' => [
            'local' => [
                'activemq-connect-check' => true,
                'activemq-send-recive-ack-check' => true,
            ],
        ],
        'mountfs_check' => [
            'local_web_docker' => [
                'glusterfs-mount-check' => true,
                'glusterfs-write-read-delete-check' => true,
            ],
            'local_ssh' => [
                'mountfs-mount-check' => true,
                'mountfs-write-read-delete-check' => true,
            ],
        ],
        'processing_check' => [
            'local' => [
                'processing-min-amount-workers-check' => true,
                'processing-status-workers-check' => [
                    'incident_type' => 'warning',
                ],
            ],
        ],
        'email_check' => [
            'local' => [
                'email-send-check' => true,
                'email-receive-check' => true,
            ],
        ],
    ],
];
