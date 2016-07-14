<?php

namespace TonicHealthCheck\Tests\Integration\Http;

use Http\Mock\Client as MockClient;
use TonicHealthCheck\Tests\Integration\AbstractIntegration;

/**
 * Class AbstractHttpCheckTest.
 */
abstract class AbstractHttpCheckTest extends AbstractIntegration
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
        parent::setUp();
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
