<?php
namespace Dibs\EasyCheckout\Model\Config\Source;

use Dibs\EasyCheckout\Model\Config;

/**
 * Class Carrier
 * @package Dibs\EasyCheckout\Model\Config\Source
 */
class CustomerTypes implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray() {
        return [Config::DIBS_CUSTOMER_TYPE_B2C => 'B2C only', 
                Config::DIBS_CUSTOMER_TYPE_B2B => 'B2B only',
                Config::DIBS_CUSTOMER_TYPE_ALL_B2C_DEFAULT => 'B2C & B2B (defaults to B2C)',
                Config::DIBS_CUSTOMER_TYPE_ALL_B2B_DEFAULT => 'B2B & B2C (defaults to B2B)'];
    }

}
