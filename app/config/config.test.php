<?php

return array_replace_recursive(
    require __DIR__.'/config.php',
    require __DIR__.'/parameter.default.php',
    [
        'doctrine' => [
            'dbs' => [
                'default' => [
                    'driver' => 'pdo_sqlite',
                    'path' => false,
                    'memory' =>  true
                ],
            ],
        ],
    ]

);
