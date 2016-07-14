# Health board engine
[![License](https://img.shields.io/github/license/tonicforhealth/health-board-engine.svg?maxAge=2592000)](LICENSE.md)
[![Build Status](https://travis-ci.org/tonicforhealth/health-board-engine.svg?branch=master)](https://travis-ci.org/tonicforhealth/health-board-engine)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tonicforhealth/health-board-engine/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tonicforhealth/health-board-engine/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/tonicforhealth/health-board-engine/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/tonicforhealth/health-board-engine/?branch=master)

Health board engine is app that performs all checks that we can set up in parameters.php. This system combines together many types of the service checks to one interface. After it does some checking, it sends results to console and cachet.
## Requirements
------------

- PHP 5.5 or higher
- ext-imap
- ext-pdo
- ext-ssh2

## Installation using [Composer](http://getcomposer.org/)
------------

```bash
git clone git@github.com:tonicforhealth/health-board-engine.git
cd health-board-engine
composer install
cp app/config/parameter.default.php app/config/parameter.php
```

## Setup
------------

For the correct run of health-board-engine app you need to install 2 tools:

 - [Cachet](https://github.com/CachetHQ/Cachet)
 - [health-checker-incident](https://github.com/tonicforhealth/health-checker-incident)

Then set up config for app/config/parameter.php:
       
    ...
    'incident' => [
        'base_url' => 'http://localhost:8080',
        'config' => [
            'username' => 'test@gmail.com',
            'password' => 'test',
        ],
    ],
    'rest' => [
        'cachet' => [
            'base_url' => 'http://localhost:8000/api/v1',
            'config' => [
                //'username' => 'test@gmail.com',
                //'password' => 'test',
                'token'=> 'tVDnR5BQtu1BXKKnH3zG',
            ],
        ],
    ],
    ...

## Run of all checks
------------

```bash
app/console healthcheck:all
```
## Run with docker
All info about it you can find here [docker-image-health-board](https://bitbucket.org/tonicforhealth/docker-image-health-board)

## What service checks it has
------------

 - http
 - db
 - elasticsearch
 - redis
 - activemq
 - glusterfs
 - processing
 - email 

## Creating your own new checker
------------

It's simple: you just need to implement [AbstractCheck](https://github.com/tonicforhealth/health-checker-check/blob/master/src/AbstractCheck.php) interface then add it to [services.php](https://github.com/tonicforhealth/health-board-engine/blob/master/app/config/services.php)
