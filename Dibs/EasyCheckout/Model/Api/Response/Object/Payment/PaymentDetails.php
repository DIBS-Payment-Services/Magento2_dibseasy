<?php
namespace Dibs\EasyCheckout\Model\Api\Response\Object\Payment;

use Magento\Framework\DataObject;

/**
 * Class Payment
 * @package Dibs\EasyCheckout\Model\Api\Response\Object
 */
class PaymentDetails
{

    /** @var  string */
    private $paymentType;

    /** @var  string */
    private $paymentMethod;

    /** @var DataObject|null  */
    private $invoiceDetails;

    /** @var DataObject|null  */
    private $cardDetails;

    /**
     * Dibs_EasyCheckout_Model_Api_Payment_Consumer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->paymentType = isset($data['paymentType']) ? $data['paymentType'] : null;
        $this->paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : null ;
        $this->invoiceDetails = isset($data['invoiceDetails']) ? new DataObject($data['invoiceDetails']) : null;
        $this->cardDetails = isset($data['cardDetails']) ? new DataObject($data['cardDetails']) : null;
    }

    /**
     * @return null|string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @return null|string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return DataObject|null
     */
    public function getInvoiceDetails()
    {
        return $this->invoiceDetails;
    }

    /**
     * @return DataObject|null
     */
    public function getCardDetails()
    {
        return $this->cardDetails;
    }

    /**
     * @return null|string
     */
    public function getMaskedPan()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)){
            $result = $cardDetails->getData('maskedPan');
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcLast4()
    {
        $result = null;
        $maskedPan = $this->getMaskedPan();

        if ($maskedPan != '') {
            $result = substr($maskedPan, -4);
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcExpMonth()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)) {
            $expiryDate = $cardDetails->getData('expiryDate');
            if ($expiryDate != '') {
                $result = substr($expiryDate, 0, -2);
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcExpYear()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)) {
            $expiryDate = $cardDetails->getData('expiryDate');
            if ($expiryDate != '') {
                $result = substr($expiryDate, -2);
            }
        }

        return $result;
    }

}