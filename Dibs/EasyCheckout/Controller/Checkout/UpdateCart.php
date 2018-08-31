<?php
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Checkout;

class UpdateCart extends \Magento\Framework\App\Action\Action {


   private $dibsCheckout;

   public function __construct(\Magento\Framework\App\Action\Context $context, 
                                Checkout $dibsCheckout) {
       parent::__construct($context);
       
       $this->dibsCheckout = $dibsCheckout;
   }

    public function execute() {
        $post = $this->getRequest()->getPostValue();
        if(isset($post['shipping_method'])) {
            $this->dibsCheckout->updateCartShipping($post['shipping_method']);
        }

        if(isset($post['remove_item_id'])) {
           $this->dibsCheckout->removeFromCart($post['remove_item_id']);
        }

    }

}

