<?php
namespace Dibs\EasyCheckout\Model;

/**
 * Class Config
 * @package Dibs\EasyCheckout\Model
 */
class Config
{
    const PAYMENT_CHECKOUT_METHOD = 'dibs_easy_checkout';

    const DIBS_FREE_SHIPPING_METHOD_CODE = 'dibs_free_shipping';

    const DIBS_TERMS_CONDITIONS_CONFIG_LINK_TYPE_FIELD = 'terms_and_conditions_link_type';

    const DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT_FIELD = 'terms_and_conditions_link';

    const DIBS_TERMS_CONDITIONS_CONFIG_TYPE_CMS_PAGE_FIELD = 'terms_and_conditions_link_cms';

    const DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT = 'direct';

    const DIBS_TERMS_CONDITIONS_CONFIG_TYPE_CMS_PAGE = 'cms_page';

    const API_ENVIRONMENT_TEST = 'test';

    const API_ENVIRONMENT_LIVE = 'live';

    const DIBS_CHECKOUT_JS_TEST = 'https://test.checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DIBS_CHECKOUT_JS_LIVE = 'https://checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DEFAULT_CHECKOUT_LANGUAGE = 'en-GB';

    const DIBS_CUSTOMER_TYPE_B2B = 'B2B';

    const DIBS_CUSTOMER_TYPE_B2C = 'B2C';

    const DIBS_CUSTOMER_TYPE_ALL_B2B_DEFAULT = 'B2B_B2C';

    const DIBS_CUSTOMER_TYPE_ALL_B2C_DEFAULT = 'B2C_B2B';

    // For now we support only SEK
    protected $_supportedCurrencies = ['SEK','DKK','NOK'];

    protected $_supportedLanguages = ['en-GB','sv-SE','nb-NO','da-DK'];

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var \Magento\Store\Model\StoreManagerInterface  */
    protected $storeManager;

    /** @var Magento\Cms\Helper\Page  */
    protected $cmsPageHelper;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Helper\Page $cmsPageHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->cmsPageHelper = $cmsPageHelper;
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
     * @return mixed
     */
    public function getAllowedCustomerTypes()
    {
        $result = $this->getConfigParam('allowed_customer_types');
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

    /**
     * @return mixed
     */
    public function getTermsAndConditionsUrl()
    {
        $result = '';
        $linkType = $this->getConfigParam(self::DIBS_TERMS_CONDITIONS_CONFIG_LINK_TYPE_FIELD);
        switch ($linkType) {
            case self::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT:
                $result = $this->getConfigParam(self::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT_FIELD);
                break;
            case self::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_CMS_PAGE:
                $cmsPageId = $this->getConfigParam(self::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_CMS_PAGE_FIELD);
                $result = $this->cmsPageHelper->getPageUrl($cmsPageId);
                break;
            default:
                //Compatibility with older versions
                $result = $this->getConfigParam(self::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT_FIELD);
                break;

        }

        return $result;
    }



}