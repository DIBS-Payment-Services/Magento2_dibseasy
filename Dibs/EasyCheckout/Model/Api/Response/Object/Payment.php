<?php
namespace Dibs\EasyCheckout\Model\Api\Response\Object;

use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\PaymentDetails;
use Magento\Framework\DataObject;

/**
 * Class Payment
 * @package Dibs\EasyCheckout\Model\Api\Response\Object
 */
class Payment
{

    /** @var  string */
    private $paymentId;

    /** @var DataObject  */
    private $summary;

    /** @var Consumer  */
    private $consumer;

    /** @var DataObject  */
    private $paymentDetails;

    /** @var DataObject  */
    private $orderDetails;

    /** @var DataObject  */
    private $checkout;

    /** @var null|DataObject  */
    private $refunds;

    /** @var \DateTime  */
    private $created;

    /**
     * Payment constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->paymentId = $data['paymentId'];
        $this->created = new \DateTime($data['created']);
        $this->summary = new DataObject($data['summary']);
        $this->consumer = new Consumer($data['consumer']);
        $this->paymentDetails = new PaymentDetails($data['paymentDetails']);
        $this->orderDetails = new DataObject($data['orderDetails']);
        $this->checkout = new DataObject($data['checkout']);
        $this->refunds = isset($data['refunds']) ? new DataObject($data['refunds']) : null;
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return Consumer\Address
     */
    public function getBillingAddress()
    {
        return $this->consumer->getBillingAddress();
    }

    /**
     * @return Consumer\Address
     */
    public function getShippingAddress()
    {
        return $this->consumer->getShippingAddress();
    }

    /**
     * @return Consumer\Company
     */
    public function getCompany()
    {
        return $this->consumer->getCompany();
    }

    /**
     * @return Consumer\PrivatePerson
     */
    public function getPrivatePerson()
    {
        return $this->consumer->getPrivatePerson();
    }

    /**
     * @return PaymentDetails|DataObject
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    /**
     * @return DataObject
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    /**
     * @return DataObject
     */
    public function getCheckout()
    {
        return $this->checkout;
    }

    /**
     * @return DataObject|null
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    public function getSummary()
    {
        return $this->summary;
    }
    
    public function getEmail() {
        
        $email = $this->getPrivatePerson()->getData('email'); 
        if(!$email) {
            $email = $this->getCompany()->getData('contactDetails')['email'];
        }
        return $email;
    }
}
