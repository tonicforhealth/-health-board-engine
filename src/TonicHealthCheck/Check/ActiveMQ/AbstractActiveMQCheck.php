<?php

namespace TonicHealthCheck\Check\ActiveMQ;

use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractActiveMQCheck.
 */
abstract class AbstractActiveMQCheck extends AbstractCheck
{
    const COMPONENT = 'activemq';
    const GROUP = 'web';
}
