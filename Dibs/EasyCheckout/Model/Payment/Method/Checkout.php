<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Model\Payment\Method;

use Dibs\EasyCheckout\Model\Api;
use Dibs\EasyCheckout\Model\Config;
use Dibs\EasyCheckout\Model\Exception;
use Magento\Paypal\Model\Express\Checkout as ExpressCheckout;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Quote\Model\Quote;

/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Model\Payment\Method
 */
class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = Config::PAYMENT_CHECKOUT_METHOD;

    /**
     * @var string
     */
    protected $_infoBlockType = \Dibs\EasyCheckout\Block\Info::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;


    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;


    /**
     * @var Config
     */
    protected $dibsEasyCheckoutConfig;

    /**
     * @var Api
     */
    protected $dibsApi;

    /**
     * Checkout constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param Api $dibsApi
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        Api $dibsApi,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->dibsApi = $dibsApi;


    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     *
     * @return $this
     * @throws Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
         parent::capture($payment,$amount);
        /** @var Invoice $invoice */
        $invoice = $payment->getInvoice();

        if (!$invoice){
            throw new Exception(__('Invoice is not exists'));
        }

        $chargeId = $this->processCharge($invoice, $amount);

        if (empty($chargeId)){
            throw new Exception(__('Transaction is not processed. Please contact administrator'));
        }

        $dibsPayment = $this->getDibsPayment($invoice);

        $payment->setData('dibs_easy_cc_masked_pan', $dibsPayment->getPaymentDetails()->getMaskedPan());
        $payment->setData('cc_last_4',$dibsPayment->getPaymentDetails()->getCcLast4());
        $payment->setData('cc_exp_month',$dibsPayment->getPaymentDetails()->getCcExpMonth());
        $payment->setData('cc_exp_year',$dibsPayment->getPaymentDetails()->getCcExpYear());

        $payment->setStatus(self::STATUS_APPROVED);
        $payment->setTransactionId($chargeId)
            ->setIsTransactionClosed(1);

        return $this;
    }

    /**
     * @param Invoice $invoice
     * @param $amount
     *
     * @return mixed|null
     */
    public function processCharge(Invoice $invoice, $amount) {
        $chargeId = null;

        try {
            $chargeId = $this->dibsApi->chargePayment($invoice, $amount);
        } catch (Api\Exception $exception){
            $this->_logger->error($exception);
        }

        return $chargeId;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     *
     * @return $this
     * @throws Exception
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $chargeId = null;

        /** @var Creditmemo $creditMemo */
        $creditMemo = $payment->getCreditmemo();

        /** @var Invoice $invoice */
        $invoice = $creditMemo->getInvoice();

        if ($invoice && $invoice->getTransactionId()){
            $chargeId = $invoice->getTransactionId();
        }

        if (empty($chargeId)){
            throw new Exception(__('Dibs Charge id is empty'));
        }

        $refundId = $this->processRefund($creditMemo,$amount,$chargeId);

        if (empty($refundId)){
            throw new Exception(__('Transaction is not processed. Please contact administrator'));
        }

        $payment->setTransactionId($refundId)
            ->setIsTransactionClosed(1);

        return $this;
    }

    /**
     * @param Creditmemo $creditmemo
     * @param $amount
     * @param $chargeId
     *
     * @return mixed|null
     */
    public function processRefund(Creditmemo $creditmemo, $amount, $chargeId) {
        $refundId = null;
        try {
            $refundId = $this->dibsApi->refundPayment($chargeId, $creditmemo, $amount);
        } catch (Api\Exception $exception) {
            $this->_logger->error($exception);
        }
        return $refundId;
    }

    /**
     * @param $invoice
     *
     * @return Api\Response\Object\Payment|null
     */
    protected function getDibsPayment($invoice)
    {
        $paymentId = $invoice->getOrder()->getDibsEasyPaymentId();
        $dibsPayment = $this->dibsApi->findPayment($paymentId);

        return $dibsPayment;
    }

    /**
     * Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
        return false;
    }


    /**
     * Check whether payment method can be used
     * @param \Magento\Quote\Api\Data\CartInterface|Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) ;
    }


}
