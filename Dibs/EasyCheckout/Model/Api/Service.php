<?php
namespace Dibs\EasyCheckout\Model\Api;

/**
 * Class Service
 * @package Dibs\EasyCheckout\Model\Api
 */
class Service
{

    /**
     * @var Client
     */
    private $client;

    /**
     * Service constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
