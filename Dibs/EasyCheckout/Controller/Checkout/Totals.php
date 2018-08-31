<?php
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Checkout;
use Magento\Framework\Controller\ResultFactory;

class Totals extends \Magento\Framework\App\Action\Action { 

    private $dibsCheckout;

    private $resultPageFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                 Checkout $dibsCheckout,
                                 ResultFactory $resultFactory) {
        parent::__construct($context);
        $this->dibsCheckout = $dibsCheckout;
        $this->resultPageFactory = $resultFactory;
    }

    public function execute() {
      echo $this->dibsCheckout->getCartTotals();
    }
}
