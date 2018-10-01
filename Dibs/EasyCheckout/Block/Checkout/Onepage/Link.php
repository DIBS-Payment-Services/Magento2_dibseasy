<?php
namespace Dibs\EasyCheckout\Block\Checkout\Onepage;

class Link extends \Magento\Checkout\Block\Onepage\Link {
    
    
  public function getCheckoutUrl() {
      return $this->getUrl('dibs_easy/checkout/start');
  }
    
}