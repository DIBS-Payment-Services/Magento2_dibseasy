<?php
namespace Dibs\EasyCheckout\Model\Config\Source;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Carrier
 * @package Dibs\EasyCheckout\Model\Config\Source
 */
class Carrier implements \Magento\Framework\Option\ArrayInterface
{

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface  */
    private $scopeConfig;

    /** @var \Magento\Store\Model\StoreManagerInterface  */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => Config::DIBS_FREE_SHIPPING_METHOD_CODE, 'label' => __('Dibs Easy Free Shipping')],
        ];

        if ($this->isFlatrateActive()) {

            $label = $this->scopeConfig->getValue(
                'carriers/flatrate/title',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->getStore()
            );

            $options[] = ['value' => 'flatrate', 'label' =>$label];
        }

        return $options;
    }

    /**
     * @return bool
     */
    private function isFlatrateActive()
    {
        $isFlatrateActive = $this->scopeConfig->isSetFlag(
            'carriers/flatrate/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );

        return $isFlatrateActive;
    }

    /**
     * @return int
     */
    private function getStore()
    {
        return $this->storeManager->getStore()->getId();
    }
}
