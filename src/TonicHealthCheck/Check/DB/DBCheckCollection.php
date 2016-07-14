<?php

namespace TonicHealthCheck\Check\DB;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class DBCheckCollection.
 */
class DBCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractDBCheck::class;
}
