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
class ValidatePaymentId implements ObserverInterface
{

    /**
     * Validate Payment Id
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        $grandTotal = (double)$quote->getGrandTotal();
        $dibsEasyGrandTotal = (double)$quote->getDibsEasyGrandTotal();
        if ($grandTotal != $dibsEasyGrandTotal){
            $quote->setDibsEasyPaymentId('');
        }
    }
}
