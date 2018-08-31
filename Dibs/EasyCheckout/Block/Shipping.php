<?php
namespace Dibs\EasyCheckout\Block;

use Magento\Framework\View\Element\Template;
use Dibs\EasyCheckout\Model\Checkout;
/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Block
 */
class Shipping extends Template
{
    
    protected $dibsCheckout;
    protected $request;
    
    public function __construct(Template\Context $context, 
                                array $data = array(),
                                Checkout $dibsCheckout,
                                \Magento\Framework\App\Request\Http $request) {
        parent::__construct($context, $data);
        
        $this->dibsCheckout = $dibsCheckout;
        $this->request = $request;
    }
    
    public function getShippingMethods() {
        $post = $this->request->getPostValue(); 
         if(isset($post['countrycode'])) {
           return $this->dibsCheckout->getShippingMethods($post['countrycode']);
        }
    }
}