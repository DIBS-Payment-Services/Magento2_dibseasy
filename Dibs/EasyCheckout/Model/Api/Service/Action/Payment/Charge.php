<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Model\Api\Service\Action\Payment;
use Dibs\EasyCheckout\Model\Api\Service\Action\AbstractAction;

/**
 * Class Charge
 * @package Dibs\EasyCheckout\Model\Api\Service\Action\Payment
 */
class Charge extends AbstractAction {

    protected $apiEndpoint = '/payments';

    protected $orderFields = [
        'amount',
        'orderItems'
    ];

    protected $orderItemFields = [
        'reference',
        'name',
        'quantity',
        'unit',
        'unitPrice',
        'taxRate',
        'taxAmount',
        'grossTotalAmount',
        'netTotalAmount'
    ];

    /**
     * @param $paymentId
     *
     * @return string
     */
    protected function getApiEndpoint($paymentId)
    {
        $url = $this->getClient()->getApiUrl() . $this->apiEndpoint . '/' . $paymentId . '/charges';
        return $url;
    }

    /**
     * @param $paymentId
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function request($paymentId, $params)
    {
        $this->validateRequest($params);
        $apiEndPoint = $this->getApiEndpoint($paymentId);
        $response = $this->getClient()->request($apiEndPoint,'POST', $params);
        $this->validateResponse($response);
        return $response;
    }

    /**
     * @param $response
     *
     * @return $this
     * @throws \Dibs\EasyCheckout\Model\Api\Exception\Response
     */
    protected function validateResponse($response)
    {
        $responseArray = $response->getResponseArray();
        if (!isset($responseArray['chargeId']) && !empty($responseArray['chargeId'])){
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Response(__('PaymentId is empty'));
        }

        return $this;
    }

    /**
     * @param $params
     *
     * @return $this
     * @throws \Dibs\EasyCheckout\Model\Api\Exception\Request
     */
    protected function validateRequest($params)
    {
        $missedParams = [];

        if (!isset($params['amount'])){
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Parameter amount is missing'));
        }

        if (!isset($params['orderItems']) || empty($params['orderItems'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order items'));
        }

        foreach ($params['orderItems'] as $orderItem){
            foreach ($this->orderItemFields as $orderItemField){
                if (!isset($orderItem[$orderItemField])){
                    $missedParams[] = $orderItemField;
                }
            }
            if (!empty($missedParams)){
                throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order item fields ') . implode(',',$missedParams));
            }
        }

        return $this;

    }




}