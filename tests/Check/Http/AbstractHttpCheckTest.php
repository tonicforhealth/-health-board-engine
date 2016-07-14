<?php

namespace TonicHealthCheck\Tests\Check\Http;

use Http\Mock\Client as MockClient;
use PHPUnit_Framework_TestCase;

/**
 * Class AbstractHttpCheckTest.
 */
abstract class AbstractHttpCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MockClient
     */
    protected $mockClient;

    /**
     * set up.
     */
    public function setUp()
    {
        $this->setMockClient(new MockClient());
    }

    /**
     * @return MockClient
     */
    public function getMockClient()
    {
        return $this->mockClient;
    }

    /**
     * @param MockClient $mockClient
     */
    protected function setMockClient(MockClient $mockClient)
    {
        $this->mockClient = $mockClient;
    }
}
