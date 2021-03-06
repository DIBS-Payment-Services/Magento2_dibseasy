<?php
namespace Dibs\EasyCheckout\Model\Api\Service;
use Dibs\EasyCheckout\Model\Api\Client;
use Dibs\EasyCheckout\Model\Api\Service;

/**
 * Class Refund
 * @package Dibs\EasyCheckout\Model\Api\Service
 */
class Refund extends Service
{

    /**
     * @var Action\Refund\Charge
     */
    private $charge;

    /**
     * Refund constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->charge = new Service\Action\Refund\Charge($this);
        parent::__construct($client);
    }

    /**
     * @param $chargeId
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function charge($chargeId, $params)
    {
        $result = $this->charge->request($chargeId, $params);
        return $result;
    }
}
