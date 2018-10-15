<?php


namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\CheckoutManager;



/**
 * Description of UpdateView
 *
 * @author maxwhite
 */
class UpdateView extends \Magento\Framework\App\Action\Action {

    protected $dibsCheckoutManager;

    
    public function __construct(\Magento\Framework\App\Action\Context $context, 
                                CheckoutManager $dibsCheckoutManager) {
        parent::__construct($context);
        
        $this->dibsCheckoutManager = $dibsCheckoutManager;
    }
    
    public function execute() {
        
        $post = $this->getRequest()->getPostValue();
        
        try {
        switch ($post['action']) {
            
            case 'change_shipping' : 
                    $this->dibsCheckoutManager->setShippingMethod($post['method']);
                break;
            
            case 'update_qty' : 
                    $this->dibsCheckoutManager->updateCartQty($post['id'], $post['qty']);
                break;
            
            case 'remove_item' : 
                    $this->dibsCheckoutManager->removeCartItem($post['id']);
                break;
            
            case 'change_address': 
                    $this->dibsCheckoutManager->changeShippingAddress();
                break;
            
            case 'cart_items': 
                    $this->dibsCheckoutManager->getCartItems();
                break;
            
            case 'start':
                    $this->dibsCheckoutManager->start();
                break;
            
        }
        } catch(\Exception $e) {
            echo json_encode(['exception' => $e->getMessage()]);
            exit;
        }
        
        echo $this->dibsCheckoutManager->getGridValues();
        
    }

}
