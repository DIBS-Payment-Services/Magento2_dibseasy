<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer;
use Dibs\EasyCheckout\Model\Api\Response;
use Magento\Framework\DataObject;

/**
 * Class Address
 * @package Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer
 */
class Address extends DataObject {

    /**
     * @return array
     */
    public function getStreetsArray()
    {
        $result = [
            $this->getData('addressLine1'),
            $this->getData('addressLine2')
        ];
        return $result;
    }


}