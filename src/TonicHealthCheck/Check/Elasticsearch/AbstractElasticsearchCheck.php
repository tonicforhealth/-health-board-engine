<?php

namespace TonicHealthCheck\Check\Elasticsearch;

use TonicHealthCheck\Check\AbstractCheck;

/**
 * Class AbstractElasticsearchCheck.
 */
abstract class AbstractElasticsearchCheck extends AbstractCheck
{
    const COMPONENT = 'elasticsearch';
    const GROUP = 'web';
}
