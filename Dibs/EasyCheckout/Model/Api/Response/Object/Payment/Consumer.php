<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Model\Api\Response\Object\Payment;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\Address;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\Company;
use Dibs\EasyCheckout\Model\Api\Response\Object\Payment\Consumer\PrivatePerson;

/**
 * Class Consumer
 * @package Dibs\EasyCheckout\Model\Api\Response\Object\Payment
 */
class Consumer {

    /** @var Address  */
    protected $billingAddress;

    /** @var Address  */
    protected $shippingAddress;

    /** @var Company  */
    protected $company;

    /** @var PrivatePerson  */
    protected $privatePerson;

    /**
     * Consumer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->billingAddress = new Address($data['billingAddress']);
        $this->shippingAddress = new Address($data['shippingAddress']);
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