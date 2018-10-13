<?php
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Checkout;
use Magento\Framework\Controller\ResultFactory;

class Shipping extends \Magento\Framework\App\Action\Action {
    protected $dibsCheckout;
    protected $resultFactory;
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                 Checkout $dibsCheckout,
                                 ResultFactory $resultFactory) {
        parent::__construct($context);
        $this->dibsCheckout = $dibsCheckout;
        $this->resultFactory = $resultFactory;
    }

    public function execute() {
        
       $shippingMethods = $this->dibsCheckout->getShippingMethods();
       $result = json_decode($shippingMethods, true);
       if($result['result'] == 'error') {
          $this->messageManager->addErrorMessage($result['message']);
       }
       echo $shippingMethods;
       
   }
}