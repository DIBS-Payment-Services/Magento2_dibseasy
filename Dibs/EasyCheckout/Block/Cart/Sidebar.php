<?php

namespace Dibs\EasyCheckout\Block\Cart;

/**
 * Description of Sidebar
 *
 * @author mabe
 */
class Sidebar extends  \Magento\Checkout\Block\Cart\Sidebar {
    
    public function getCheckoutUrl()
    {
        return $this->getUrl('dibs_easy/checkout/start');
    }
}
