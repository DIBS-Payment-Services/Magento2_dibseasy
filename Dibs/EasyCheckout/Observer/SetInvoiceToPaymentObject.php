<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Class AddDibsEasyShortcutsObserver
 * @package Dibs\EasyCheckout\Observer
 */
class SetInvoiceToPaymentObject implements ObserverInterface
{

    /**
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $payment = $observer->getEvent()->getPayment();
        $payment->setInvoice($invoice);
        return $this;
    }
}
