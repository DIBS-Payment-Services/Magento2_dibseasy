<?php
namespace Dibs\EasyCheckout\Block;

/**
 * Base payment iformation block
 */
class Info extends \Magento\Payment\Block\Info
{

    const DIBS_EASY_MASKED_PAN = 'dibs_easy_cc_masked_pan';
    const DIBS_EASY_PAYMENT_ID = 'dibs_easy_payment_id';
    const DIBS_EASY_PAYMENT_TYPE = 'dibs_easy_payment_type';


    /**
     * Prepare information specific to current payment method
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $paymentSpecificInformation = parent::_prepareSpecificInformation($transport);

        $paymentIdLabel = __('Payment ID')->getText();
        $maskedPanLabel = __('Masked Pan')->getText();
        $paymentTypeLabel = __('Payment Type')->getText();

        $paymentData = [];

        $paymentData[$paymentIdLabel] =$this->getInfo()->getOrder()->getData(self::DIBS_EASY_PAYMENT_ID) ;

        $dibsEasyPaymentType = $this->getInfo()->getData(self::DIBS_EASY_PAYMENT_TYPE);
        if (!empty($dibsEasyPaymentType)){
            $paymentData[$paymentTypeLabel] = $dibsEasyPaymentType;
        }

        $dibsEasyMaskedPan = $this->getInfo()->getData(self::DIBS_EASY_MASKED_PAN);
        if (!empty($dibsEasyMaskedPan)){
            $paymentData[$maskedPanLabel] = $dibsEasyMaskedPan;
        }

        $paymentSpecificInformation->addData($paymentData);
        $this->_paymentSpecificInformation = $paymentSpecificInformation;
        return $this->_paymentSpecificInformation;
    }
}