<?php

namespace TonicHealthCheck\Check\ActiveMQ;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class ActiveMQCheckCollection.
 */
class ActiveMQCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractActiveMQCheck::class;
}
