<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Block\Checkout;

use Dibs\EasyCheckout\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Button
 * @package Dibs\EasyCheckout\Block\Checkout
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const DIBS_EASY_BUTTON_ID = 'dibs-easy-checkout-button';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    const CART_BUTTON_ELEMENT_INDEX = 'add_to_cart_selector';


    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * Button constructor.
     *
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Config $config
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Config $config,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->config = $config;
        $this->checkoutHelper = $checkoutHelper;
    }


    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('dibs_easy/checkout/start');
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
       $result = $this->checkoutHelper->getQuote();
       return $result;
    }
    /**
     * @return bool
     */
    protected function shouldRender()
    {
        $result = false;
        if ($this->isMiniCart && $this->config->isDibsEasyCheckoutAvailable($this->getQuote())){
            $result = true;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getAddToCartSelector()
    {
        return $this->getData(self::CART_BUTTON_ELEMENT_INDEX);
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }
}
