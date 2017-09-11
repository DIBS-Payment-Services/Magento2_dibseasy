<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Model;

use Dibs\EasyCheckout\Model\Api\Client;
use Dibs\EasyCheckout\Model\Api\Service\Payment;
use Dibs\EasyCheckout\Model\Api\Service\Refund;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class Api
 * @package Dibs\EasyCheckout\Model
 */
class Api
{

    /** @var Config  */
    protected $config;

    /** @var \Magento\Framework\UrlInterface  */
    protected $urlBuilder;

    public function __construct(Config $config, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /** @var Client  */
    protected $apiClient;

    /**
     * @var Payment
     */
    protected $paymentService;

    /**
     * @var Refund
     */
    protected $refundService;

    /**
     * @param Quote $quote
     *
     * @return null
     */
    public function createPayment(Quote $quote)
    {
        $result = null;
        $paymentService = $this->getPaymentService();
        $createPaymentParams = $this->getCreatePaymentParams($quote);
        $response = $paymentService->create($createPaymentParams);
        $result = $response->getResponseDataObject()->getData('paymentId');
        return $result;
    }

    /**
     * @param $paymentId
     *
     * @return Api\Response\Object\Payment|null
     */
    public function findPayment($paymentId)
    {
        $result = null;
        $paymentService = $this->getPaymentService();
        $response = $paymentService->find($paymentId);
        $result = new \Dibs\EasyCheckout\Model\Api\Response\Object\Payment($response->getResponseDataObject()->getData('payment'));
        return $result;
    }

    /**
     * @param Invoice $invoice
     * @param $amount
     *
     * @return mixed|null
     */
    public function chargePayment(Invoice $invoice, $amount)
    {
        $result = null;
        $paymentId = $invoice->getOrder()->getDibsEasyPaymentId();
        $paymentService = $this->getPaymentService();
        $chargeParams = $this->getChargePaymentParams($invoice, $amount);
        $response = $paymentService->charge($paymentId, $chargeParams);
        $result = $response->getResponseDataObject()->getData('chargeId');
        return $result;
    }

    /**
     * @param $chargeId
     * @param $creditmemo
     * @param $amount
     *
     * @return mixed|null
     */
    public function refundPayment($chargeId, $creditmemo, $amount)
    {
        $result = null;
        $refundService = $this->getRefundService();
        $chargeParams = $this->getRefundPaymentParams($creditmemo, $amount);
        $response = $refundService->charge($chargeId, $chargeParams);
        $result = $response->getResponseDataObject()->getData('refundId');
        return $result;
    }

    /**
     * @return Payment
     */
    public function getPaymentService()
    {
        if (is_null($this->paymentService)){
            $apiClient = $this->getApiClient();
            $this->paymentService = new Payment($apiClient);
        }

        return $this->paymentService;
    }

    /**
     * @return Refund
     */
    public function getRefundService()
    {
        if (is_null($this->refundService)){
            $apiClient = $this->getApiClient();
            $this->refundService = new Refund($apiClient);
        }

        return $this->refundService;
    }

    /**
     * @return Client
     */
    protected function getApiClient()
    {
        if (is_null($this->apiClient)){
            $secretKey = $this->config->getSecretKey();
            $isTestEnvironment = $this->config->isTestEnvironmentEnabled();
            $this->apiClient = new Client($secretKey, $isTestEnvironment);
        }
        return $this->apiClient;
    }

    /**
     * @param $creditmemo
     * @param $amount
     *
     * @return array
     */
    protected function getRefundPaymentParams($creditmemo, $amount)
    {
        $refundOrderItems = $this->getCreditMemoItems($creditmemo);
        $params = [
            'amount' => $this->getDibsIntVal($amount),
            'orderItems' => $refundOrderItems
        ];

        return $params;
    }

    /**
     * @param Invoice $invoice
     * @param $amount
     *
     * @return array
     */
    protected function getChargePaymentParams(Invoice $invoice, $amount)
    {
        $invoiceItems = $this->getInvoiceItems($invoice);
        $params = [
            'amount' => $this->getDibsIntVal($amount),
            'orderItems' => $invoiceItems
        ];

        return $params;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    protected function getCreatePaymentParams(Quote $quote)
    {
        $params = [
            'order' => [
                'items'     =>  $this->getQuoteItems($quote),
                'amount'    =>  $this->getDibsQuoteGrandTotal($quote),
                'currency'  =>  $quote->getQuoteCurrencyCode(),
                'reference' =>  $quote->getEntityId()
            ],
            'checkout' => [
                'url' => $this->urlBuilder->getUrl('dibs_easy/checkout/start'),
            ]
        ];

        return $params;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    protected function getQuoteItems(Quote $quote)
    {
        $result = [];
        $items = $quote->getAllItems();
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item){
            if ($this->isNotChargeable($item)){
                continue;
            }
            $result[] = $this->getOrderLineItem($item);

        }

        $shippingAddress = $quote->getShippingAddress();
        if ($shippingAddress->getShippingAmount() > 0){
            $shippingReference = $shippingAddress->getShippingMethod();
            $shippingName = $shippingAddress->getShippingDescription();
            $result[] = $this->getShippingLine($shippingAddress, $shippingReference, $shippingName);
        }

        return $result;
    }

    protected function getShippingLine($shipping, $shippingReference, $shippingName)
    {
        $name = preg_replace('/[^\w\d\s]*/','',$shippingName);
        $result = [
            'reference'         =>  $shippingReference,
            'name'              =>  $name,
            'quantity'          =>  1,
            'unit'              =>  1,
            'unitPrice'         =>  $this->getDibsIntVal($shipping->getShippingAmount()),
            'taxRate'           =>  0,
            'taxAmount'         =>  $this->getDibsIntVal($shipping->getShippingTaxAmount()),
            'grossTotalAmount'  =>  $this->getDibsIntVal($shipping->getShippingInclTax()),
            'netTotalAmount'    =>  $this->getDibsIntVal($shipping->getShippingAmount()),
        ];

        return $result;
    }

    /**
     * @param Creditmemo $creditMemo
     *
     * @return array
     */
    protected function getCreditMemoItems(Creditmemo $creditMemo)
    {
        $result = [];
        $items = $creditMemo->getAllItems();
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($items as $item){
            if ($this->isNotChargeable($item->getOrderItem())){
                continue;
            }
            $result[] = $this->getOrderLineItem($item);

        }

        $shippingInclTaxAmount = (double)$creditMemo->getShippingInclTax();

        if ($shippingInclTaxAmount > 0){
            $shippingReference = $creditMemo->getOrder()->getShippingMethod();
            $shippingName = $creditMemo->getOrder()->getShippingDescription();
            $result[] = $this->getShippingLine($creditMemo,$shippingReference, $shippingName);
        }

        return $result;
    }


    /**
     * @param Invoice $invoice
     *
     * @return array
     */
    protected function getInvoiceItems(Invoice $invoice)
    {
        $result = [];
        $items = $invoice->getAllItems();
        /** @var Invoice\Item $item */
        foreach ($items as $item){
            if ($this->isNotChargeable($item->getOrderItem())){
                continue;
            }
            $result[] = $this->getOrderLineItem($item);

        }

        $shippingInclTaxAmount = (double)$invoice->getShippingInclTax();

        if ($shippingInclTaxAmount > 0){
            $shippingReference = $invoice->getOrder()->getShippingMethod();
            $shippingName = $invoice->getOrder()->getShippingDescription();
            $result[] = $this->getShippingLine($invoice,$shippingReference, $shippingName);
        }

        return $result;
    }

    /**
     * @param $item
     *
     * @return array
     */
    protected function getOrderLineItem( $item)
    {
        $name = preg_replace('/[^\w\d\s]*/','',$item->getSku());
        $result = [
            'reference'         =>  $item->getSku(),
            'name'              =>  $name,
            'quantity'          =>  (int)$item->getQty(),
            'unit'              =>  1,
            'unitPrice'         =>  $this->getDibsIntVal($item->getPrice()),
            'taxRate'           =>  $this->getDibsIntVal($item->getTaxPercent()),
            'taxAmount'         =>  $this->getItemTaxAmount($item),
            'grossTotalAmount'  =>  $this->getItemGrossTotalAmount($item),
            'netTotalAmount'    =>  $this->getItemNetTotalAmount($item) ,
        ];

        return $result;
    }

    /**
     * @param Quote $quote
     *
     * @return int
     */
    public function getDibsQuoteGrandTotal(Quote $quote)
    {
        return $this->getDibsIntVal($quote->getGrandTotal());
    }


    /**
     * @param $item
     *
     * @return bool
     */
    protected function isNotChargeable( $item)
    {
        $result = false;
        if ($item->getParentItem()
            && $item->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $result = true;
        }

        if ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param $item
     *
     * @return int
     */
    protected function getItemTaxAmount( $item)
    {
        $itemTax = (double)$item->getTaxAmount();
        $result = $this->getDibsIntVal($itemTax);

        return $result;
    }

    /**
     * @param $item
     *
     * @return int
     */
    protected function getItemGrossTotalAmount( $item)
    {
        $itemGrossTotal = (double)$item->getRowTotalInclTax() - (double)$item->getDiscountAmount();
        $result = $this->getDibsIntVal($itemGrossTotal);

        return $result;
    }

    /**
     * @param $item
     *
     * @return int
     */
    protected function getItemNetTotalAmount( $item)
    {
        $netDiscount = (double)$item->getDiscountAmount() - (double)$item->getDiscountTaxCompensationAmount();
        $itemNetTotal = (double)$item->getRowTotalInclTax() - (double)$item->getTaxAmount() - $netDiscount;
        $result = $this->getDibsIntVal($itemNetTotal);

        return $result;
    }

    /**
     * @param $value
     *
     * @return int
     */
    public function getDibsIntVal($value)
    {
        $result = (double)$value * 100;
        return (int)$result;
    }

    /**
     * @param $value
     *
     * @return float
     */
    public function convertDibsValToRegular($value)
    {
        $result = $value / 100;
        return (double)$result;
    }

}
