<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Block;

/**
 * Base payment iformation block
 */
class Info extends \Magento\Payment\Block\Info
{

    /**
     * Prepare information specific to current payment method
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $paymentSpecificInformation = parent::_prepareSpecificInformation($transport);
        $paymentData = ['Payment ID'=> $this->getInfo()->getOrder()->getDibsEasyPaymentId()];
        $paymentSpecificInformation->addData($paymentData);
        $this->_paymentSpecificInformation = $paymentSpecificInformation;
        return $this->_paymentSpecificInformation;
    }
}