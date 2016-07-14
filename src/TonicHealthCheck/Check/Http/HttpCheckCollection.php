<?php

namespace TonicHealthCheck\Check\Http;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class HttpCheckCollection.
 */
class HttpCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractHttpCheck::class;
}
