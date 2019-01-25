<?php
namespace Dibs\EasyCheckout\Block;

use Dibs\EasyCheckout\Model\Api;
use Dibs\EasyCheckout\Model\Config;
use Dibs\EasyCheckout\Model\Exception;
use Magento\Framework\View\Element\Template;

/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Block
 */
class Checkout extends Template
{

    /** @var Config  */
    protected $config;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var Api  */
    protected $api;

    /** @var \Magento\Checkout\Helper\Cart  */
    protected $cartHelper;

    /** @var \Magento\Checkout\Model\Cart  */
    protected $cart;

    /** @var \Dibs\EasyCheckout\Model\Checkout  */
    protected $dibsCheckout;

    /** @var \Magento\Framework\Message\ManagerInterface  */
    protected $messageManager;

    protected $allmethods;

    protected $currency;

    protected $quoteIdMaskFactory;
    
    protected $response;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                Config $config,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Dibs\EasyCheckout\Model\Checkout $dibsCheckout,
                                \Magento\Framework\Message\ManagerInterface $messageManager,
                                \Magento\Shipping\Model\Config $allmethods,
                                \Magento\Directory\Model\Currency $currency,
                                \Magento\Framework\App\Response\Http $response)
    {
        $this->dibsCheckout = $dibsCheckout;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->allmethods = $allmethods;
        $this->currency = $currency;
        $this->response = $response;
        parent::__construct($context);
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function getPaymentId()
    {
        $quote = $this->getQuote();
        if (empty($quote->getDibsEasyPaymentId())){
            try {
                 $this->dibsCheckout->createPaymentId($quote);
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->response->setRedirect($this->getUrl('checkout/cart'));
            }

        }
        return $quote->getDibsEasyPaymentId();
    }


    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutKey()
    {
        return $this->config->getCheckoutKey();
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutLanguage()
    {
        return $this->config->getCheckoutLanguage();
    }

    /**
     * @return string
     */
    public function getDibsCheckoutJsUrl()
    {
        return $this->config->getEasyCheckoutJsUrl();
    }

    /**
     * @return string
     */
    public function getDibsUpdateCartUrlPattern()
    {
        return $this->config->getUpdateCartUrl();
    }

    /**
     * @return string
     */
    public function getDibsCheckoutValidateUrl()
    {
        return $this->getUrl('dibs_easy/checkout/validate');
    }

    public function getCurrency() {
        return $this->currency;
    }
    
    public function getShippingAddressId() {
        $quote = $this->getQuote();
        return $quote->getShippingAddress()->getId();
    }
    
    public function getCartId() {
        $quote = $this->getQuote();
        return $quote->getId();
    }
    
    public function couponeIsApplied() {
        $quote = $this->getQuote();
        if($quote->getCouponCode()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function couponCode() {
        $quote = $this->getQuote();
        return $quote->getCouponCode();
    }
}
