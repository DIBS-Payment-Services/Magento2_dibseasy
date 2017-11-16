<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Model;

/**
 * Class Config
 * @package Dibs\EasyCheckout\Model
 */
class Config
{
    const PAYMENT_CHECKOUT_METHOD = 'dibs_easy_checkout';

    const DIBS_FREE_SHIPPING_METHOD_CODE = 'dibs_free_shipping';

    const API_ENVIRONMENT_TEST = 'test';

    const API_ENVIRONMENT_LIVE = 'live';

    const DIBS_CHECKOUT_JS_TEST = 'https://test.checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DIBS_CHECKOUT_JS_LIVE = 'https://checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DEFAULT_CHECKOUT_LANGUAGE = 'en-GB';

    // For now we support only SEK
    protected $_supportedCurrencies = ['SEK','DKK','NOK'];

    protected $_supportedLanguages = ['en-GB','sv-SE','nb-NO','da-DK'];

    protected $scopeConfig;

    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        $result = $this->getConfigParam('active');
        return $result;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = $this->getTestSecretKey();
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = $this->getLiveSecretKey();
                break;
            default:
                $result = '';
        }


        return $result;

    }

    /**
     * @return mixed|string
     */
    public function getCheckoutKey()
    {
        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = $this->getTestCheckoutKey();
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = $this->getLiveCheckoutKey();
                break;
            default:
                $result = '';
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getEasyCheckoutJsUrl()
    {
        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = self::DIBS_CHECKOUT_JS_TEST;
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = self::DIBS_CHECKOUT_JS_LIVE;
                break;
            default:
                $result = '';
        }


        return $result;

    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        $result = $this->getConfigParam('environment');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getTestSecretKey()
    {
        $result = $this->getConfigParam('test_secret_key');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getLiveSecretKey()
    {
        $result = $this->getConfigParam('live_secret_key');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getTestCheckoutKey()
    {
        $result = $this->getConfigParam('test_checkout_key');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getLiveCheckoutKey()
    {
        $result = $this->getConfigParam('live_checkout_key');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getNewOrderStatus()
    {
        $result = $this->getConfigParam('new_order_status');
        return $result;
    }

    /**
     * @return mixed
     */
    public function getCarrier()
    {
        $result = $this->getConfigParam('carrier');
        return $result;
    }

    /**
     * @param $paramName
     *
     * @return mixed
     */
    protected function getConfigParam($paramName)
    {
        $paramName = 'payment/dibs_easy_checkout/'.$paramName;

        $result = $this->scopeConfig->getValue(
            $paramName,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $result;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function isTestEnvironmentEnabled()
    {
        $result = false;
        $currentEnv = $this->getEnvironment();
        if ($currentEnv == self::API_ENVIRONMENT_TEST){
            $result = true;
        }

        return $result;
    }

    /**
     * @param $quote
     *
     * @return bool
     */
    public function isDibsEasyCheckoutAvailable($quote)
    {
        $result = false;

        $active = (bool)(int) $this->getActive();
        $currencyCode = $quote->getQuoteCurrencyCode();
        if (empty($currencyCode)){
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        }
        $currencySupported = in_array($currencyCode,$this->_supportedCurrencies);

        if ($active && $currencySupported){
            $result = true;
        }

        return $result;
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutLanguage()
    {
        $language = self::DEFAULT_CHECKOUT_LANGUAGE;
        $localeCode =  $this->scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $currentStoreLang = str_replace('_','-',$localeCode);
        if (in_array($currentStoreLang, $this->_supportedLanguages)){
            $language = $currentStoreLang;
        }

        return $language;

    }





}