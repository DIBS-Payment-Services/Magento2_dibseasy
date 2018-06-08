<?php
namespace Dibs\EasyCheckout\Model\Api\Service\Action\Payment;

use Dibs\EasyCheckout\Model\Api\Service\Action\AbstractAction;

/**
 * Class Find
 * @package Dibs\EasyCheckout\Model\Api\Service\Action\Payment
 */
class Find extends AbstractAction
{

    private $apiEndpoint = '/payments';

    /**
     * @param $paymentId
     *
     * @return string
     */
    private function getApiEndpoint($paymentId)
    {
        $url = $this->getClient()->getApiUrl() . $this->apiEndpoint. '/' . $paymentId;
        return $url;
    }

    /**
     * @param $paymentId
     *
     * @return \Dibs\EasyCheckout\Model\Api\Response
     * @throws \Dibs\EasyCheckout\Model\Api\Exception\Request
     */
    public function request($paymentId)
    {
        if (empty($paymentId)) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Request(__('Empty paymentId'));
        }
        $apiEndPoint = $this->getApiEndpoint($paymentId);
        $response = $this->getClient()->request($apiEndPoint, 'GET');
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
        if (!isset($responseArray['payment']) && !empty($responseArray['payment'])) {
            throw new \Dibs\EasyCheckout\Model\Api\Exception\Response(__('PaymentId is empty'));
        }

        return $this;
    }

}