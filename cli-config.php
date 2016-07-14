<?php

set_time_limit(0);

use TonicHealthCheck\Bootstrap\Kernel;

define('AUTOLOAD_PATH', __DIR__.'/vendor/autoload.php');

// include the composer autoloader
require_once AUTOLOAD_PATH;

$kernel = new Kernel('prod');
$kernel->boot();

use Doctrine\ORM\Tools\Console\ConsoleRunner;

return ConsoleRunner::createHelperSet($kernel->getContainer()['doctrine.em']);
