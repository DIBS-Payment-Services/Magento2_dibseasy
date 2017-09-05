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
class AddDibsEasyShortcutsObserver implements ObserverInterface
{
    /**
     * Add PayPal shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        $block = 'Dibs\EasyCheckout\Block\Checkout\Button';

        $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();

        /** @var \Magento\Framework\View\Element\Template $shortcut */
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            $block,
            '',
            $params
        );

        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        );
        
        $shortcutButtons->addShortcut($shortcut);
    }
}
