<?php
namespace Dibs\EasyCheckout\Block;

use Magento\Framework\View\Element\Template;
use Dibs\EasyCheckout\Model\Checkout;

/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Block
 */
class Totals extends Template {
    
    
    protected $dibsCheckout;
    
    public function __construct(Template\Context $context, 
                                array $data = array(),
                                Checkout $dibsCheckout) {
        parent::__construct($context, $data);
        $this->dibsCheckout = $dibsCheckout;
    }
    
    
    public function getCartTotals() {
        return $this->dibsCheckout->getCartTotals();
    }
    
    
}