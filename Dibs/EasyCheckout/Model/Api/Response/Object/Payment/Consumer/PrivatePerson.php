<?php
namespace Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer;

use Magento\Framework\DataObject;

/**
 * Class PrivatePerson
 * @package Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer
 */
class PrivatePerson extends DataObject
{

    /**
     * @return mixed|null
     */
    public function getTelephone()
    {
        $result = null;
        $phoneNumberArray = $this->getData('phoneNumber');
        if (!empty($phoneNumberArray)) {
            $result = $phoneNumberArray['prefix'] . $phoneNumberArray['number'];
        }

        return $result;
    }
}