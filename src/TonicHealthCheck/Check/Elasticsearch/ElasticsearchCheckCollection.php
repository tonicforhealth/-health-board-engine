<?php

namespace TonicHealthCheck\Check\Elasticsearch;

use TonicHealthCheck\Check\AbstractCheckCollection;

/**
 * Class ElasticsearchCheckCollection.
 */
class ElasticsearchCheckCollection extends AbstractCheckCollection
{
    const OBJECT_CLASS = AbstractElasticsearchCheck::class;
}
