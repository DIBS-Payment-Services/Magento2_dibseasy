<?php
namespace Dibs\EasyCheckout\Model\Api\Service\Action\Refund;

use Dibs\EasyCheckout\Model\Api\Exception\Response;
use Dibs\EasyCheckout\Model\Api\Service\Action\AbstractAction;

/**
 * Class Charge
 * @package Dibs\EasyCheckout\Model\Api\Service\Action\Refund
 */
class Charge extends AbstractAction
{

    private $apiEndpoint = '/charges';

    private $orderFields = [
        'amount',
        'orderItems'
    ];

    private $orderItemFields = [
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
     * @param $chargeId
     *
     * @return string
     */
    public function getApiEndpoint($chargeId)
    {
        $url = $this->getClient()->getApiUrl() . $this->apiEndpoint . '/' . $chargeId . '/refunds';
        return $url;
    }

    /**
     * @param $chargeId
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function request($chargeId, $params)
    {
        $this->validateRequest($params);
        $apiEndPoint = $this->getApiEndpoint($chargeId);
        $response = $this->getClient()->request($apiEndPoint,'POST', $params);
        $this->validateResponse($response);
        return $response;
    }

    /**
     * @param $response
     *
     * @return $this
     * @throws Response
     */
    private function validateResponse($response)
    {
        $responseArray = $response->getResponseArray();
        if (!isset($responseArray['refundId']) && !empty($responseArray['refundId'])) {
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
    private function validateRequest($params)
    {
        $missedParams = [];

        if (!isset($params['amount'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Parameter amount is missing'));
        }

        if (!isset($params['orderItems']) || empty($params['orderItems'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order items'));
        }

        foreach ($params['orderItems'] as $orderItem) {
            foreach ($this->orderItemFields as $orderItemField) {
                if (!isset($orderItem[$orderItemField])) {
                    $missedParams[] = $orderItemField;
                }
            }
            if (!empty($missedParams)) {
                throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order item fields ').implode(',',$missedParams));
            }
        }

        return $this;
    }
}