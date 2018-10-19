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
    
    protected $cartChanged = 0;
    
    protected $cartIsEmpty = 0;
    
    public function __construct(Checkout $dibsCheckout) {
        $this->dibsCheckout = $dibsCheckout;
    }
    
    public function setShippingMethod($shippingMethod) {
        $this->dibsCheckout->setSippingMethod($shippingMethod);
        $this->updateShippingMethod();
        $this->updateEasyCart();
    }
    
    public function updateCartQty($id, $qty) {
        $this->dibsCheckout->updateCartItemQty($id, $qty);
        $this->updateShippingMethod();
        $this->updateEasyCart();
        $this->cartChanged = 1;
    }
    
    public function removeCartItem($id) {
       $this->dibsCheckout->removeFromCart($id);
       
       $items = $this->dibsCheckout->getQuote()->getAllItems();
       
       if(count($items)) {
          $this->updateShippingMethod();
          $this->updateEasyCart();
          $this->cartChanged = 1;
       } else {
           $this->cartIsEmpty = 1;
       }
    }
    
    public function start() {
        $quote = $this->dibsCheckout->getQuote();
        if($quote->getDibsEasyPaymentId()) {
            $this->updateShippingMethod();
            if($this->cartEasyUpdateIsNeeded()) {
                $this->updateEasyCart();
            }
            
        }
        $this->cartChanged = 1;
    }
    
    public function changeShippingAddress() {
        $this->dibsCheckout->changeShippingAddress();
        $this->updateShippingMethod();
        if($this->cartEasyUpdateIsNeeded()) {
                $this->updateEasyCart();
        }
    }
    
    public function getCartItems() {
        $result['cart_items'] = $this->getCartProducts();
        echo json_encode($result);
    }
    
    protected function updateEasyCart() {
        $quote = $this->dibsCheckout->getQuote();
        $result = [];
        $result['amount'] =round($quote->getGrandTotal(), 2) * 100;
        $result['items'] = $this->dibsCheckout->api->getQuoteItems($quote);
        $result['shipping']['costSpecified'] = true;
        $paymentService = $this->dibsCheckout->api->getPaymentService();
        $paymentId = $this->dibsCheckout->getQuote()->getDibsEasyPaymentId();
        $paymentService->update($paymentId, $result);
    }
    
    protected function getSubtotal() {
        
    }
    
    protected function getGrandTotal() {
        return $this->dibsCheckout->api->getDibsIntVal($this->dibsCheckout->getQuote()->getGrandTotal());
    }
    
    protected function getShippingMethod() {
        
    }
    
    protected function getShippingMethods() {
        
    }
    
    public function updateShippingMethod() {
        $quote = $this->dibsCheckout->getQuote();
        if(!$quote->isVirtual()) {
            $methods = $this->dibsCheckout->getShippingMethodsManager();
            $quoteMethod = $quote->getShippingAddress()->getShippingMethod();
            $shippingAddress = $quote->getShippingAddress();
            if(empty($quoteMethod)) {
                if(isset($methods['methods']) && $methods['methods']) {
                    $current = current($methods['methods']);
                    $this->dibsCheckout->setSippingMethod($current['code']);
                }
            } else {
                if(isset($methods['methods']) && $methods['methods']) {
                    $current = current($methods['methods']);
                    if(!array_key_exists($quoteMethod, $methods['methods'])) {
                        $this->dibsCheckout->setSippingMethod($current['code']);
                    } else {
                        $this->dibsCheckout->setSippingMethod($quoteMethod); 
                    }
                }
            }
        } else {
            $quote->getShippingAddress()->setShippingMethod('')->save();
            
        }
    }
    
    public function getGridValues() {
        
        if(!$this->cartIsEmpty) {
            $shippingMethods = $this->dibsCheckout->getShippingMethodsManager();
            $result['totals'] = $this->dibsCheckout->getCartTotalsManager();
            $result['shipping'] = $shippingMethods;
            $result['cart_items'] = [];
            $result['redirect'] = $this->cartIsEmpty;
            if($this->cartChanged) {
               $result['cart_items'] = $this->getCartProducts();
            }
        } else {
           $result['redirect'] = $this->cartIsEmpty;
        }
        echo json_encode($result);
    }
    
    protected function getCartProducts() {
        return $this->dibsCheckout->getCartProducts();
    }
    
    protected function cartEasyUpdateIsNeeded() {
      $paymentId = $this->dibsCheckout->getQuote()->getDibsEasyPaymentId();
      
      $payment = $this->dibsCheckout->api->findPayment($paymentId);
      if($payment->getOrderDetails()->getData('amount') == $this->dibsCheckout->api->getDibsIntVal($this->getGrandTotal())) {
          return false;
      } else {
          return true;
      }
    
    }
}
