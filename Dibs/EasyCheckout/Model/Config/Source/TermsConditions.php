<?php
namespace Dibs\EasyCheckout\Model\Config\Source;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Environment
 * @package Dibs\EasyCheckout\Model\Config\Source
 */
class TermsConditions implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @return array
     */
    public function toOptionArray()
    {

        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => Config::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_DIRECT, 'label' => __('Direct Link')],
            ['value' => Config::DIBS_TERMS_CONDITIONS_CONFIG_TYPE_CMS_PAGE, 'label' => __('Cms Page')],
        ];
        return $options;
    }
}
