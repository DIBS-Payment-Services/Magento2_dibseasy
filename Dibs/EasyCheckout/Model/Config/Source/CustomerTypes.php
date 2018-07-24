<?php
namespace Dibs\EasyCheckout\Model\Config\Source;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Environment
 * @package Dibs\EasyCheckout\Model\Config\Source
 */
class CustomerTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => Config::DIBS_CUSTOMER_TYPE_B2C,
                'label' => __('(B2C) Only')
            ],
            [
                'value' => Config::DIBS_CUSTOMER_TYPE_B2B,
                'label' => __('(B2B) Only')
            ],
            [
                'value' => Config::DIBS_CUSTOMER_TYPE_ALL_B2C_DEFAULT,
                'label' => __('(B2C & B2B) Defaults to B2C')
            ],
            [
                'value' => Config::DIBS_CUSTOMER_TYPE_ALL_B2B_DEFAULT,
                'label' => __('(B2B & B2C) Defaults to B2B')
            ],

        ];
        return $options;
    }
}
