<?php

namespace TonicHealthCheck\Check\MountFS;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class MountFSCheckCollection.
 */
class MountFSCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractMountFSCheck::class;
}
