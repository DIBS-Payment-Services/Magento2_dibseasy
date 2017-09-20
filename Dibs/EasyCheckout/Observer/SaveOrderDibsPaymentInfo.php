<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class AddDibsEasyShortcutsObserver
 * @package Dibs\EasyCheckout\Observer
 */
class SaveOrderDibsPaymentInfo implements ObserverInterface
{

    /**
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $orderPayment = $order->getPayment();
        $quotePayment = $quote->getPayment();

        $order->setData('dibs_easy_payment_id',$quote->getData('dibs_easy_payment_id'));
        $orderPayment->setData('dibs_easy_cc_masked_pan',$quotePayment->getData('dibs_easy_cc_masked_pan'));
        $orderPayment->setData('dibs_easy_payment_type', $quotePayment->getData('dibs_easy_payment_type'));
        return $this;
    }
}
