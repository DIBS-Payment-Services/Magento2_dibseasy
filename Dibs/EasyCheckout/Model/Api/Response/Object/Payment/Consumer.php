<?php
namespace Dibs\EasyCheckout\Model\Api\Response\Object\Payment;

use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\Address;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\Company;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\PrivatePerson;

/**
 * Class Consumer
 * @package Dibs\EasyCheckout\Model\Api\Response\Object\Payment
 */
class Consumer
{

    /** @var Address  */
    private $billingAddress;

    /** @var Address  */
    private $shippingAddress;

    /** @var Company  */
    private $company;

    /** @var PrivatePerson  */
    private $privatePerson;

    /**
     * Consumer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
         $address = new Address($data['shippingAddress']);
         $this->shippingAddress = $address;
          // set billing the same as shipping
         $this->billingAddress = $address;
         $this->company = new Company($data['company']);
         $this->privatePerson = new PrivatePerson($data['privatePerson']);
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return PrivatePerson
     */
    public function getPrivatePerson()
    {
        return $this->privatePerson;
    }
}
