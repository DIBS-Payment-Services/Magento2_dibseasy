<?php

namespace Dibs\EasyCheckout\Controller\Checkout;

/**
 * Class Start
 * @package Dibs\EasyCheckout\Controller\Checkout
 */
class Index extends \Magento\Framework\App\Action\Action {
    
    public function execute() {
       $this->_redirect('dibs_easy/checkout/start');
    }
}