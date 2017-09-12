<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Controller\Checkout;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Start
 * @package Dibs\EasyCheckout\Controller\Checkout
 */
class Start extends \Magento\Framework\App\Action\Action {

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
        Config $config
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->checkoutHelper = $checkoutHelper;

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
        return $this->resultPageFactory->create();
    }
}