<?php
namespace Dibs\EasyCheckout\Model\Api\Service\Action\Payment;

use Dibs\EasyCheckout\Model\Api\Service\Action\AbstractAction;

/**
 * Class Create
 * @package Dibs\EasyCheckout\Model\Api\Service\Action\Payment
 */
class Create extends AbstractAction
{

    private $apiEndpoint = '/payments';

    private $orderFields = [
        'items',
        'amount',
        'currency',
        'reference'
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

    private $checkoutFields = ['url'];

    /**
     * @return string
     */
    private function getApiEndpoint()
    {
        $url = $this->getClient()->getApiUrl() . $this->apiEndpoint;
        return $url;
    }

    /**
     * @param $params
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     */
    public function request($params)
    {

        $this->validateRequest($params);
        $apiEndPoint = $this->getApiEndpoint();
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
    private function validateResponse($response)
    {
        $responseArray = $response->getResponseArray();
        if (!isset($responseArray['paymentId']) && !empty($responseArray['paymentId'])){
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

        if (!isset($params['order'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Parameter order is missing'));
        }

        foreach ($this->orderFields as $orderField) {
            if (!isset($params['order'][$orderField])) {
                $missedParams[] = $orderField;
            }
        }
        if (!empty($missedParams)) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order fields ') . implode(',',$missedParams));
        }

        if (!isset($params['order']['items']) || empty($params['order']['items'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order items'));
        }

        $itemsTotal = 0;
        foreach ($params['order']['items'] as $orderItem) {
            foreach ($this->orderItemFields as $orderItemField) {
                if (!isset($orderItem[$orderItemField])) {
                    $missedParams[] = $orderItemField;
                }
            }
            if (!empty($missedParams)) {
                throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty order item fields ') . implode(',',$missedParams));
            }

            $itemsTotal += $orderItem['grossTotalAmount'];
        }

        if ($itemsTotal != $params['order']['amount']) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Order amount not equal with items total amount'));
        }
        return $this;
    }
}