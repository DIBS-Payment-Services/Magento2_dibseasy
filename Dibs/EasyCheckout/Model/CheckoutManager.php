<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Dibs\EasyCheckout\Model;

/**
 * Description of CheckoutManager
 *
 * @author maxwhite
 */

use Dibs\EasyCheckout\Model\Checkout;

class CheckoutManager {
    
    protected $dibsCheckout;
    
    public function __construct(Checkout $dibsCheckout) {
        $this->dibsCheckout = $dibsCheckout;
    }
    
    public function setShippingMethod($shippingMethod) {
        
    }
    
    public function updateCartQty($id, $qty) {
        
    }
    
    public function removeCartItem($id) {
        
    }
    
    public function changeShippingAddress($address) {
        
    }
    
    protected function updateEasyCart() {
        
    }
    
    public function getGridValues() {
        $result['shipping'] = $this->dibsCheckout->getShippingMethodsManager();
        $result['totals'] = $this->dibsCheckout->getCartTotalsManager();
        echo json_encode($result);
    }
    
    
    
}
