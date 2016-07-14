<?php

namespace TonicHealthCheck\Checker;

use Collections\Collection;
use TonicHealthCheck\Check\CheckCollectionInterface;
use TonicHealthCheck\Check\CheckInterface;

class ChecksList extends Collection
{
    const OBJECT_CLASS = CheckInterface::class;

    public function __construct()
    {
        parent::__construct(static::OBJECT_CLASS);
    }

    /**
     * Add an item to the collection.
     *
     * @param mixed $item An item of your Collection's object type to be added
     */
    public function add($item)
    {
        if ($item instanceof CheckCollectionInterface) {
            foreach ($item as $itemOfCollection) {
                parent::add($itemOfCollection);
            }
        } else {
            parent::add($item);
        }
    }
}
