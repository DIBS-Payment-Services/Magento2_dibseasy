<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Api;
use Dibs\EasyCheckout\Model\Checkout;


/**
 * Class Validate
 * @package Dibs\EasyCheckout\Controller\Checkout
 */
class Validate extends \Magento\Framework\App\Action\Action {

    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var Api */
    protected $dibsApi;

    /** @var Checkout  */
    protected $dibsCheckout;

    /** @var \Psr\Log\LoggerInterface  */
    protected $logger;


    /**
     * Validate constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Api $dibsApi
     * @param Checkout $dibsCheckout
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        Api $dibsApi,
        Checkout $dibsCheckout,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->dibsApi = $dibsApi;
        $this->dibsCheckout = $dibsCheckout;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }


    public function execute()
    {
        $quote = $this->checkoutSession->getQuote();
        $paymentId = $quote->getDibsEasyPaymentId();

        if (empty($paymentId)){
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        try {

            $payment = $this->dibsApi->findPayment($paymentId);
            $isValidEmail = $this->dibsCheckout->isValidEmail($payment);

            if (!$isValidEmail){
                $this->messageManager->addErrorMessage(__("Email is not valid"));
                $this->dibsCheckout->resetDibsQuoteData($quote);

                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }

            $isValidPayment = $this->dibsCheckout->validatePayment($quote, $payment);
            if ($isValidPayment) {
                $this->dibsCheckout->place($quote, $payment);
            } else {
                $this->messageManager->addErrorMessage(__("The payment data and order data doesn't appear to match, please try again"));
                $this->dibsCheckout->resetDibsQuoteData($quote);

                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
            }

        } catch (\Dibs\EasyCheckout\Model\Exception $e) {
            $message  = __('There is error. Please contact store administrator for details');
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage($message);
            $this->dibsCheckout->resetDibsQuoteData($quote);
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');

        } catch (\Exception $e) {
            $this->dibsCheckout->resetDibsQuoteData($quote);
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success', ['_secure' => true]);
    }

}