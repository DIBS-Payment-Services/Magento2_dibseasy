<?php
namespace Dibs\EasyCheckout\Model\Api\Service\Action;

use Dibs\EasyCheckout\Model\Api\Service;

/**
 * Class AbstractAction
 * @package Dibs\EasyCheckout\Model\Api\Service\Action
 */
abstract class AbstractAction
{

    /** @var Service  */
    private $service;

    /**
     * AbstractAction constructor.
     *
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return \Dibs\EasyCheckout\Model\Api\Client
     */
    public function getClient()
    {
        return $this->getService()->getClient();
    }
}