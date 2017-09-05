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
class SaveOrderDibsPaymentId implements ObserverInterface
{

    /**
     * Set gift messages to order from quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getEvent()->getOrder()->setDibsEasyPaymentId($observer->getEvent()->getQuote()->getDibsEasyPaymentId());

        return $this;
    }
}
