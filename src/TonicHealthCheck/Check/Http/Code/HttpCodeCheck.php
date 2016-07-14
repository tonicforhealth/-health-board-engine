<?php

namespace TonicHealthCheck\Check\Http\Code;

use Http\Client\Common\HttpMethodsClient;
use TonicHealthCheck\Check\Http\AbstractHttpCheck;

/**
 * Class HttpCheckCode.
 */
class HttpCodeCheck extends AbstractHttpCheck
{
    const CHECK = 'http-code-check';

    const AVERAGE_MAX_TIME = 2.0;
    const AVERAGE_TIME_TRY_COUNT = 5;
    const DEFAULT_HTTP_CODE = 200;

    /**
     * @var HttpMethodsClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $defaultUrl = null;

    /**
     * @var float
     */
    protected $averageMaxTime = self::AVERAGE_MAX_TIME;

    /**
     * @var int
     */
    protected $tryCount = self::AVERAGE_TIME_TRY_COUNT;

    /**
     * @param null              $checkNode
     * @param HttpMethodsClient $client
     * @param string            $defaultUrl
     */
    public function __construct($checkNode, HttpMethodsClient $client, $defaultUrl)
    {
        parent::__construct($checkNode);
        $this->setClient($client);
        $this->setDefaultUrl($defaultUrl);
    }

    /**
     * Check $url for 200 code or other.
     *
     * @param string $url
     * @param int    $code
     *
     * @return bool|void
     *
     * @throws HttpCodeCheckException
     */
    public function check($url = null, $code = self::DEFAULT_HTTP_CODE)
    {
        if (null === $url) {
            $url = $this->getDefaultUrl();
        }

        try {
            $res = $this->getClient()->get($url);
        } catch (\Exception $e) {
            throw new HttpCodeCheckException($e->getMessage(), $e->getCode(), $e);
        }

        if ($res->getStatusCode() != $code) {
            throw HttpCodeCheckException::unexpectedHttpCode($res->getStatusCode(), $code);
        }

        return true;
    }

    /**
     * @return string
     */
    public function getDefaultUrl()
    {
        return $this->defaultUrl;
    }

    /**
     * @return HttpMethodsClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $defaultUrl
     */
    protected function setDefaultUrl($defaultUrl)
    {
        $this->defaultUrl = $defaultUrl;
    }

    /**
     * @param HttpMethodsClient $client
     */
    protected function setClient(HttpMethodsClient $client)
    {
        $this->client = $client;
    }
}
