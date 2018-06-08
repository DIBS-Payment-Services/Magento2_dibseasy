<?php
namespace Dibs\EasyCheckout\Plugin;

use Dibs\EasyCheckout\Model\Config;
use Magento\Quote\Api\ShipmentEstimationInterface;

class ShippingEstimationPlugin
{
    /**
     * @param ShipmentEstimationInterface $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $result
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods
     */
    public function afterEstimateByExtendedAddress(ShipmentEstimationInterface $subject, $result)
    {
        $methods = [];

        foreach ($result as $shippingMethod)
        {
            if ($shippingMethod->getMethodCode() == Config::DIBS_FREE_SHIPPING_METHOD_CODE) {
                continue;
            }

            $methods[] = $shippingMethod;
        }

        return $methods;
    }
}
