<?php

namespace TonicHealthCheck\CachetHQ\Authentication;

use Http\Message\Authentication;
use Psr\Http\Message\RequestInterface;

/**
 * Authenticate a PSR-7 Request using a token.
 */
final class Token implements Authentication
{
    const COOKIE_CACHET_TOKEN = 'X-Cachet-Token';
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(RequestInterface $request)
    {
        return $request->withHeader(self::COOKIE_CACHET_TOKEN, $this->token);
    }
}
