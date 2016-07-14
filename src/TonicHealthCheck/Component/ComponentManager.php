<?php

namespace TonicHealthCheck\Component;

use Doctrine\ORM\EntityManager;
use Http\Client\Common\HttpMethodsClient;
use TonicHealthCheck\Entity\Component as ComponentE;

/**
 * Class ComponentManager.
 */
class ComponentManager
{
    /**
     * @var EntityManager
     */
    private $doctrine;

    /**
     * @var HttpMethodsClient
     */
    private $cachetRestClient;

    /**
     * @var string
     */
    private $restBaseUrl;

    /**
     * ComponentHandler constructor.
     *
     * @param EntityManager     $doctrine
     * @param HttpMethodsClient $cachetRestClient
     * @param string            $restBaseUrl
     */
    public function __construct(
        EntityManager $doctrine,
        HttpMethodsClient $cachetRestClient,
        $restBaseUrl
    ) {
        $this->setDoctrine($doctrine);
        $this->setCachetRestClient($cachetRestClient);
        $this->setRestBaseUrl($restBaseUrl);
    }

    /**
     * @return EntityManager
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @return HttpMethodsClient
     */
    public function getCachetRestClient()
    {
        return $this->cachetRestClient;
    }

    /**
     * @return string
     */
    public function getRestBaseUrl()
    {
        return $this->restBaseUrl;
    }

    /**
     * @param string $resourcePath
     *
     * @return string
     */
    public function getResourceUrl($resourcePath)
    {
        return $this->getRestBaseUrl().$resourcePath;
    }

    /**
     * @param string $name
     *
     * @return ComponentE
     */
    public function getComponentByName($name)
    {
        $component = $this->getDoctrine()
            ->getRepository(ComponentE::class)
            ->findOneBy(['name' => $name]);
        if (!$component) {
            $component = new ComponentE();
            $component
                ->setName($name);
        }

        return $component;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getComponentRes($id)
    {
        $componentData = null;
        if ($id != 0) {
            $response = $this->getCachetRestClient()->get(
                $this->getResourceUrl('/components/'.$id)
            );
            $componentData = json_decode($response->getBody()->getContents());
        }

        return $componentData;
    }

    /**
     * @param ComponentE $component
     *
     * @return mixed
     *
     * @throws ComponentException
     */
    public function createComponentRes(ComponentE $component)
    {
        $request = $this->getCachetRestClient()->post(
            $this->getResourceUrl('/components'),
            ['Content-type' => 'application/json;'],
            json_encode([
                'name' => $component->getName(),
                'status' => static::checkStatusCodeToStatus($component->getStatus()),
            ])
        );

        $response = $request->getBody()->getContents();

        $componentData = json_decode($response);

        if (null === $componentData || !empty($componentData->errors) || empty($componentData->data)) {
            $errorMsg = isset($componentData->errors[0]->detail) ? $componentData->errors[0]->detail : 'unknown';

            throw ComponentException::canNotCreateResource($errorMsg);
        }

        return $componentData->data;
    }

    /**
     * @param ComponentE $component
     */
    public function updateComponentRes(ComponentE $component)
    {
        $request = $this->getCachetRestClient()->put(
            $this->getResourceUrl('/components/'.$component->getId()),
            ['Content-type' => 'application/json'],
            json_encode([
                'status' => static::checkStatusCodeToStatus($component->getStatus()),
            ])
        );

        $response = $request->getBody()->getContents();
    }

    /**
     * @param ComponentE $component
     */
    public function saveComponent($component)
    {
        $this->getDoctrine()->persist($component);
        $this->getDoctrine()->flush();
    }

    protected static function checkStatusCodeToStatus($checkStatusCode)
    {
        return $checkStatusCode == 0 ? 1 : 4;
    }

    /**
     * @param EntityManager $doctrine
     */
    protected function setDoctrine(EntityManager $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param HttpMethodsClient $cachetRestClient
     */
    protected function setCachetRestClient(HttpMethodsClient $cachetRestClient)
    {
        $this->cachetRestClient = $cachetRestClient;
    }

    /**
     * @param string $restBaseUrl
     */
    protected function setRestBaseUrl($restBaseUrl)
    {
        $this->restBaseUrl = $restBaseUrl;
    }
}
