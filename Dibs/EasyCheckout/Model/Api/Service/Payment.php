<?php
namespace Dibs\EasyCheckout\Model\Api\Service;

use Dibs\EasyCheckout\Model\Api\Client;
use Dibs\EasyCheckout\Model\Api\Service;

/**
 * Class Payment
 * @package Dibs\EasyCheckout\Model\Api\Service
 */
class Payment extends Service
{

    /** @var Action\Payment\Create  */
    private $create;

    /** @var Action\Payment\Find  */
    private $find;

    /** @var Action\Payment\Charge  */
    private $charge;

    /** @var Action\Payment\Cancel  */
    private $cancel;

    /**
     * Payment constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->create = new Service\Action\Payment\Create($this);
        $this->find = new Service\Action\Payment\Find($this);
        $this->charge = new Service\Action\Payment\Charge($this);
        $this->cancel = new Service\Action\Payment\Cancel($this);

    }

    /**
     * @param $paymentId
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function find($paymentId)
    {
       $result = $this->find->request($paymentId);
       return $result;
    }

    /**
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function create($params)
    {
        $result = $this->create->request($params);
        return $result;
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

    /**
     * @param $paymentId
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function cancel($paymentId, $params)
    {
        $result = $this->cancel->request($paymentId, $params);
        return $result;
    }
}
