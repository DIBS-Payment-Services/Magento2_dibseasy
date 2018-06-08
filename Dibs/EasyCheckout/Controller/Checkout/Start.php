<?php
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Checkout;
use Dibs\EasyCheckout\Model\Config;

/**
 * Class Start
 * @package Dibs\EasyCheckout\Controller\Checkout
 */
class Start extends \Magento\Framework\App\Action\Action {

    const DIBS_PAYMENT_ID_PARAM = 'paymentId';
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var Checkout
     */
    protected $dibsCheckout;

    /**
     * Start constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        Checkout $dibsCheckout,
        Config $config
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->checkoutHelper = $checkoutHelper;
        $this->dibsCheckout = $dibsCheckout;

        parent::__construct($context);
    }

    public function getQuote()
    {
        return $this->checkoutHelper->getQuote();
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quote = $this->getQuote();
        if (!$this->config->isDibsEasyCheckoutAvailable($quote) || count($quote->getAllItems()) == 0){
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $paymentId = $this->getRequest()->getParam(self::DIBS_PAYMENT_ID_PARAM);
        $quoteDibsPaymentId = $quote->getDibsEasyPaymentId();

        if (!empty($paymentId) && $paymentId == $quote->getDibsEasyPaymentId()){
            return $this->resultRedirectFactory->create()->setPath('dibs_easy/checkout/validate');
        }

        if (!empty($paymentId) && !empty($quoteDibsPaymentId) && $paymentId != $quoteDibsPaymentId){
            $this->dibsCheckout->resetDibsQuoteData($quote);
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $this->resultPageFactory->create();
    }
}