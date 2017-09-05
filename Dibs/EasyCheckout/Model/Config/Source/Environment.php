<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Dibs\EasyCheckout\Model\Config\Source;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Environment
 * @package Dibs\EasyCheckout\Model\Config\Source
 */
class Environment implements \Magento\Framework\Option\ArrayInterface
{


    /**
     * @return array
     */
    public function toOptionArray()
    {

        $options = [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => Config::API_ENVIRONMENT_TEST, 'label' => __('Test')],
            ['value' => Config::API_ENVIRONMENT_LIVE, 'label' => __('Live')],
        ];
        return $options;
    }
}
