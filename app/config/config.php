<?php

return [
    'doctrine' => [
        'dbs' => [
            'default' => [
                'driver' => 'pdo_sqlite',
                'path' => 'data/sqllite/database.sqlite',
            ],
        ],
        'orm' => [
            'orm.em.options' => [
                'mappings' => [
                    [
                        'type' => 'annotation',
                        'namespace' => 'TonicHealthCheck\Entity',
                        'path' => 'src/TonicHealthCheck/Entity',

                    ],
                    'processing_check' => [
                        'type' => 'annotation',
                        'namespace' => 'TonicHealthCheck\Check\Processing\Entity',
                        'path' => ['src/TonicHealthCheck/Check/Processing/Entity'],
                    ],

                ],
            ],
        ],
    ],
    'lang' => [
        'dirs' => [
            __DIR__.'/../../src/TonicHealthCheck',
        ],
        'language' => 'en',
    ],
    'twig' => [
        'dirs' => [
            __DIR__.'/../../src/TonicHealthCheck/Resources/views/terminal',
        ],
    ],
];
