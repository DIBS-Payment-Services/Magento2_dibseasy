<?php
namespace Dibs\EasyCheckout\Model;

use Dibs\EasyCheckout\Model\Api\Response\Object\Payment;
use Magento\Framework\Validator\EmailAddress;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Model
 */
class Checkout
{

    /** @var Config  */
    protected $config;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var Api  */
    protected $api;

    /** @var \Magento\Checkout\Helper\Cart  */
    protected $cartHelper;

    /** @var \Magento\Checkout\Model\Cart  */
    protected $cart;

    /** @var \Magento\Quote\Api\CartRepositoryInterface  */
    protected $quoteRepository;

    /** @var \Magento\Quote\Api\CartManagementInterface  */
    protected $quoteManagement;

    /** @var OrderRepositoryInterface  */
    protected $orderRepository;

    /** @var \Magento\Directory\Model\RegionFactory  */
    protected $regionFactory;

    /** @var \Magento\Customer\Model\Session  */
    protected $customerSession;

    /** @var \Magento\Checkout\Helper\Data  */
    protected $checkoutHelper;

    /** @var \Magento\Directory\Model\CountryFactory  */
    protected $countryFactory;

    /** @var Order\Email\Sender\OrderSender  */
    protected $orderSender;

    /** @var \Magento\Directory\Helper\Data  */
    protected $directoryHelper;

    protected $allmethods;

    protected $rateRequestFactory;

    /**
    * @var StoreManagerInterface
    */
    private $storeManager;

    protected $currency;
    
    protected $quoteIdMaskFactory;
    
    protected $shippingManagement;

    /**
     * Checkout constructor.
     *
     * @param Config $config
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Api $api
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Directory\Helper\Data $directoryHelper
     */
    public function __construct(Config $config,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                Api $api,
                                \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
                                \Magento\Quote\Api\CartManagementInterface $quoteManagement,
                                OrderRepositoryInterface $orderRepository,
                                \Magento\Directory\Model\RegionFactory $regionFactory,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Checkout\Helper\Data $checkoutHelper,
                                \Magento\Directory\Model\CountryFactory $countryFactory,
                                Order\Email\Sender\OrderSender $orderSender,
                                \Magento\Directory\Helper\Data $directoryHelper,
                                \Magento\Shipping\Model\Config $allmethods,
                                \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
                                 StoreManagerInterface $storeManager = null,
                                \Magento\Directory\Model\Currency $currency,
                           
                                \Magento\Quote\Model\ShippingMethodManagement $shippingManagement
    )
    {
        $this->api = $api;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderRepository = $orderRepository;
        $this->regionFactory = $regionFactory;
        $this->customerSession = $customerSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->countryFactory = $countryFactory;
        $this->orderSender = $orderSender;
        $this->directoryHelper = $directoryHelper;
        $this->allmethods = $allmethods;
        $this->rateRequestFactory = $rateRequestFactory;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->currency = $currency;
      
        $this->shippingManagement = $shippingManagement;
        
    }

    /**
     * @param Quote $quote
     *
     * @return mixed
     */
    public function createPaymentId(Quote $quote)
    {
        if (!$quote->isVirtual()) {
            $rate = $this->findShippingRate($quote);
            if (!$rate || $quote->getShippingAddress()->getShippingMethod() != $rate->getCode()){
                //$this->updateShippingMethod($quote);
            }
        }
        $paymentId = $this->api->createPayment($this->getQuote());
        $this->checkoutSession->setDibsEasyPaymentId($paymentId);
        if ($paymentId) {
            $quote->setDibsEasyPaymentId($paymentId);
            $quote->setDibsEasyGrandTotal($quote->getGrandTotal());
            //$quote->save();
            $this->quoteRepository->save($quote);
        }
        return $paymentId;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    public function removeFromCart($itemId) {
         $quote = $this->getQuote();
         $quote->removeItem($itemId);
         $this->quoteRepository->save($quote);
    }

    /**
     * @param Quote $quote
     * @param Payment $payment
     *
     * @return bool
     */
    public function validatePayment( Quote $quote,  Payment $payment)
    {
        $result = false;
        if ($payment->getOrderDetails()->getData('amount') == $this->api->getDibsQuoteGrandTotal($quote)
            //&& $payment->getOrderDetails()->getData('reference') == $quote->getId()
            && $payment->getPaymentId() ==  $this->checkoutSession->getDibsEasyPaymentId()
            && $payment->getOrderDetails()->getData('currency') == $quote->getQuoteCurrencyCode()
        ) {
            $result = true;
        }

        return $result;
    }

    public function isValidEmail(Payment $payment)
    {
        $validator = new EmailAddress();
        return $validator->isValid($payment->getEmail());
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function resetDibsQuoteData(Quote $quote)
    {
        $quote->setDibsEasyPaymentId(null)
            ->setDibsEasyGrandTotal(null);
        $this->quoteRepository->save($quote);
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Payment $payment
     *
     * @throws Exception
     */
    public function place(Quote $quote, Payment $payment)
    {
        $quote->collectTotals();
        $this->prepareQuoteShippingAddress($quote, $payment);
        $this->prepareQuoteBillingAddress($quote, $payment);
        if ($this->getCheckoutMethod($quote) != \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER) {
            $this->prepareGuestQuote($quote);
        }

        $this->updatePaymentMethod($quote, $payment, Config::PAYMENT_CHECKOUT_METHOD);

        $quoteDibsTotal = $this->api->getDibsIntVal($quote->getGrandTotal());
        $reservedDibsAmount = $payment->getOrderDetails()->getData('amount');
        if ($quoteDibsTotal > $reservedDibsAmount) {
            $reservedDibsAmountRegular = $this->api->convertDibsValToRegular($reservedDibsAmount);
            $errorMessageText = 'Reserved payment amount is not correct. Reserved amount %s - order amount %s';
            $message = __($errorMessageText, $reservedDibsAmountRegular, $quote->getGrandTotal());
            throw new Exception($message);
        }

        $quote->setDibsEasyGrandTotal($quote->getGrandTotal());

        /** @var Order $order */
        $order = $this->quoteManagement->submit($quote);

        if (!$order) {
            throw new Exception(__('Order is not created'));
        }

        $order->setStatus($this->config->getNewOrderStatus());
        $this->orderRepository->save($order);

        $this->orderSender->send($order);

        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        $this->markSuccessOrder($order,$quote);

    }

    /**
     * @param $order
     * @param $quote
     */
    public function markSuccessOrder(Order $order, Quote $quote)
    {
        $this->checkoutSession
            ->setLoadInactive(false)
            ->replaceQuote($quote)
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastQuoteId($quote->getId())
            ->setLastOrderStatus($order->getStatus())
            ->setLastSuccessQuoteId($quote->getId());
    }

    /**
     * Get checkout method
     *
     * @return string
     */
    public function getCheckoutMethod(Quote $quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $quote->getCheckoutMethod();
    }

    /**
     * @param Quote $quote
     */
        /**
     * @param Quote $quote
     */
    public function updateShippingMethod(Quote $quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        if (empty($shippingAddress->getCountryId())){
            $countryId = $this->directoryHelper->getDefaultCountry();
            $shippingAddress->setCountryId($countryId);
        }
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $rate = $this->findShippingRate($quote);
        if (!$rate) {
            throw new Exception(__('There is error. Please contact store administrator for details'));
        }
        $shippingAddress->setShippingMethod($rate->getCode());
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension && $cartExtension->getShippingAssignments()) {
            $cartExtension->getShippingAssignments()[0]
                ->getShipping()
                ->setMethod($rate->getCode());
        }
        if ($rate) {
            $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
            $shippingAddress->setShippingDescription(trim($shippingDescription, ' -'));
        }
        $this->checkoutSession->setDibsEasyShippingMethodCode($rate->getCode());
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    

    /**
     * @param Quote $quote
     *
     * @return Quote\Address\Rate|null
     */
    protected function findShippingRate(Quote $quote)
    {
        $result = null;
        $carrier = $this->config->getCarrier();
        $rates = $quote->getShippingAddress()->getShippingRatesCollection();
        /** @var Quote\Address\Rate $rate */
        foreach ($rates as $rate){
            if ($rate->getCarrier() == $carrier){
                $result = $rate;
                break;
            }
        }
        return $result;
    }

    /**
     * @param Quote $quote
     * @param Payment $payment
     * @param $methodCode
     *
     * @return $this
     */
    public function updatePaymentMethod(Quote $quote, Payment $dibsPayment, $methodCode)
    {
        $payment = $quote->getPayment();
        $paymentData = [
            'method'=> $methodCode,
        ];
        $payment->importData($paymentData);
        $payment->setData('dibs_easy_payment_type', $dibsPayment->getPaymentDetails()->getPaymentType());
        $payment->setData('dibs_easy_cc_masked_pan', $dibsPayment->getPaymentDetails()->getMaskedPan());
        $payment->setData('cc_last_4',$dibsPayment->getPaymentDetails()->getCcLast4());
        $payment->setData('cc_exp_month',$dibsPayment->getPaymentDetails()->getCcExpMonth());
        $payment->setData('cc_exp_year',$dibsPayment->getPaymentDetails()->getCcExpYear());
        return $this;
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    protected function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getShippingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Payment $payment
     *
     * @return $this
     */
    protected function prepareQuoteBillingAddress(Quote $quote, Payment $payment)
    {
        $paymentBillingAddress = $payment->getBillingAddress();
        if (empty($paymentBillingAddress->getData())){
            $paymentBillingAddress = $payment->getShippingAddress();
        }
        $country = $this->countryFactory->create()->loadByCode($paymentBillingAddress->getData('country'));
        $billingAddress = $quote->getBillingAddress();
        $billingRegionCode  = $paymentBillingAddress->getData('postalCode');
        $billingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $billingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $billingAddress->setStreet($paymentBillingAddress->getStreetsArray());
        $billingAddress->setPostcode($paymentBillingAddress->getData('postalCode'));
        $billingAddress->setCity($paymentBillingAddress->getData('city'));
        $billingAddress->setCountryId($country->getCountryId());
        $billingAddress->setEmail($payment->getEmail());
        $billingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $billingAddress->setCompany($payment->getCompany()->getData('name'));
        if ($billingRegionCode) {
            $billingRegionId = $this->regionFactory->create()->loadByCode($billingRegionCode, $billingAddress->getCountryId());
            $billingAddress->setRegionId($billingRegionId->getId());
        }
        $billingAddress->setShouldIgnoreValidation(true);
        return $this;
    }

    /**
     * @param Quote $quote
     * @param Payment $payment
     */
    protected function prepareQuoteShippingAddress(Quote $quote, Payment $payment)
    {
        $country = $this->countryFactory->create()->loadByCode($payment->getShippingAddress()->getData('country'));
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $shippingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $shippingAddress->setStreet($payment->getShippingAddress()->getStreetsArray());
        $shippingAddress->setPostcode($payment->getShippingAddress()->getData('postalCode'));
        $shippingAddress->setCity($payment->getShippingAddress()->getData('city'));
        $shippingAddress->setCountryId($country->getCountryId());
        $shippingAddress->setEmail($payment->getEmail());
        $shippingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $shippingAddress->setCompany($payment->getCompany()->getData('name'));
        $shippingRegionCode = $payment->getShippingAddress()->getData('postalCode');

        if ($shippingRegionCode) {
            $shippingRegionId = $this->regionFactory->create()->loadByCode($shippingRegionCode, $shippingAddress->getCountryId());
            $shippingAddress->setRegionId($shippingRegionId->getId());
        }

        $shippingAddress->setShouldIgnoreValidation(true);
    }

    public function getShippingMethods() {
        $quote = $this->checkoutSession->getQuote();
        $paymentId = $this->checkoutSession->getDibsEasyPaymentId();
        $payment = $this->api->findPayment($paymentId);
        $this->prepareQuoteShippingAddress($quote, $payment);
        $this->prepareQuoteBillingAddress($quote, $payment);
        $this->quoteRepository->save($quote);
        $address = $quote->getShippingAddress();
        $address->collectShippingRates()->save();
        $shippingMethods = $this->getShippingMethodsBasedOnAddress($payment);
        $quoteShippingMethodCode = $quote->getShippingAddress()->getShippingMethod();
        // Set the first available shipping method 
        if(empty($quoteShippingMethodCode) && !empty($shippingMethods)) {
            $method = current($shippingMethods);
            $shippingMethodCode = $method->getCarrierCode() . '_' . $method->getMethodCode();
            $this->setSippingMethod($shippingMethodCode);
            $this->updateCartShipping($shippingMethodCode);
        }
        if($quoteShippingMethodCode && !empty($shippingMethods)) {
            if($this->getShippigMethodByCode($quoteShippingMethodCode)) {
                error_log('Shipping method from quote is in the list of methods');
            }else {
                error_log('Shipping method from quote is NOT in the list of methods');
            }
        }
        
        
        $return = [];
        foreach($shippingMethods as $method) {
           $code = $method->getCarrierCode() . '_' . $method->getMethodCode();
           $store = $this->storeManager->getStore();
           $amountPrice = $store->getBaseCurrency()->convert($method->getAmount(), $store->getCurrentCurrencyCode());
           $active = 0;  
           if($quote->getShippingAddress()->getShippingMethod() == $code) $active = 1;
           $return[$code] = ['carrier_title' => $method->getCarrierTitle(),
                             'price' => $this->currency->format($amountPrice, array('symbol' => ''), false, false),
                             'method_title' => $method->getMethodTitle(),
                             'code' => $code,
                             'active' => $active];
         }  
       
       return json_encode($return);
    }
    
    public function getShippingMethodsBasedOnAddress($payment) {
        $shippingAddress =  $this->getQuote()->getShippingAddress();
        $country = $this->countryFactory->create()->loadByCode($payment->getShippingAddress()->getData('country'));
        $shippingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $shippingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $shippingAddress->setStreet($payment->getShippingAddress()->getStreetsArray());
        $shippingAddress->setPostcode($payment->getShippingAddress()->getData('postalCode'));
        $shippingAddress->setCity($payment->getShippingAddress()->getData('city'));
        $shippingAddress->setCountryId($country->getCountryId());
        $shippingAddress->setEmail($payment->getEmail());
        $shippingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $shippingAddress->setCompany($payment->getCompany()->getData('name'));
        
        $shippingAddress->setShouldIgnoreValidation(true);
        $cartId = $this->getQuote()->getId();
        $return = $this->shippingManagement->estimateByExtendedAddress($cartId, $shippingAddress);
       
        return $return;
    }

    public function setSippingMethod($methodCode) {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->collectShippingRates();
        $shippingAddress->setShippingMethod($methodCode);
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension && $cartExtension->getShippingAssignments()) {
            $cartExtension->getShippingAssignments()[0]
                ->getShipping()
                ->setMethod($methodCode);
        }
        $result = $this->getShippigMethodByCode($methodCode);
        $sipping_description = '';
        if(isset($result['shipping_description'])) {
            $sipping_description = trim($result['shipping_description']);
        }
        $shippingAddress->setShippingDescription($sipping_description);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $paymentId = $this->checkoutSession->getDibsEasyPaymentId();
        $this->quoteRepository->save($quote);
    }

    public function updateCartShipping($shippingCode) {
        $orderItems = json_decode($this->checkoutSession->getOrderItems(), true);
        $quote = $this->getQuote();
        if(!$orderItems) {
           $result = ['status' => 'error'];
           echo json_encode($result);
           exit;
        }
        $this->setSippingMethod($shippingCode);
        $res = $this->getShippigMethodByCode($shippingCode);
        $shippingAmount = $res['amount'];
        $result = [];
        $shipAmount = $shippingAmount * 100;
        $result['amount'] = $this->api->getDibsIntVal($this->getQuote()->getGrandTotal());
        $result['items'] = $this->api->getCarcItems($quote);
        $result['items'][] = ["reference"=> "shipping1",
                              "name"=> "Shipping",
                              "quantity"=> 1.0,
                              "unit"=> "NA",
                              "unitPrice"=> 0,
                              "taxRate"=> 0,
                              "taxAmount"=> 0,
                              "grossTotalAmount"=> $shipAmount,
                              "netTotalAmount"=> 0]; 
        $result['shipping']['costSpecified'] = true;
        $client = ObjectManager::getInstance()->create('\Dibs\EasyCheckout\Model\Api\Client', ['secretKey' =>  $this->config->getSecretKey()]);
        $url = $client->getApiUrl() . '/payments/' . $quote->getDibsEasyPaymentId() . '/orderitems';
        $rersponse = $client->request($url, 'PUT', $result);
        if(204 == $rersponse->getCode()) {
           $shippingRates = $this->getShippigMethodByCode($shippingCode);
           $result = ['status' => 'success',
                      'subtotal' => $this->currency->format($this->getQuote()->getSubtotal(),array('symbol' => ''), false, false), 
                      'grand_total' => $this->currency->format($this->getQuote()->getGrandTotal(), array('symbol' => ''), false, false),
                      'shipping' => $shippingRates['carrier_name'],
                      'currency' => $this->getQuote()->getQuoteCurrencyCode()];
           echo json_encode($result);
        } else {
           $result = ['status' => 'error'];
           echo json_encode($result);
        }
    }

    public function getShippigMethodByCode($shippingCode) {
        $quote = $this->checkoutSession->getQuote();
        $address = $quote->getShippingAddress();
        $address->collectShippingRates()->save();
        $rates = $address->getGroupedAllShippingRates();
        $shippingMethodsArr = [];
        $store = $this->storeManager->getStore();
        $result = [];
        $paymentId = $quote->getDibsEasyPaymentId();
        $payment = $this->api->findPayment($paymentId);
        $shippingMethods = $this->getShippingMethodsBasedOnAddress($payment);
        foreach($shippingMethods as $method) {
            $code = $method->getCarrierCode() . '_' . $method->getMethodCode();
            if($shippingCode == $code) {
                $result['amount'] =  $store->getBaseCurrency()
                            ->convert($method->getAmount(), $store->getCurrentCurrencyCode());
                $result['carrier_name'] =$method->getMethodTitle();
                $result['shipping_description'] = $method->getCarrierTitle() . ' - ' . $method->getMethodTitle();
                return $result;
            }
        }
        return $result;
    }

    public function getCartTotals() {
        $return = [];
        $subtotal = $this->getQuote()->getSubtotal();
        $grandTotal = $this->getQuote()->getGrandTotal();
        $addresses = $this->getQuote()->getAllShippingAddresses();
        $quote = $this->getQuote();
        $shippingCode = $quote->getShippingAddress()->getShippingMethod();
        $current = current($addresses);
        $res = $this->getShippigMethodByCode($shippingCode);
        $result = ['status' => 'success',
                   'subtotal' => $this->currency->format($subtotal, array('symbol' => ''), false, false), 
                   'grand_total' => $this->currency->format($grandTotal, array('symbol' => ''), false, false),
                   'shipping' => $res['carrier_name'],
                   'currency' => $this->getQuote()->getQuoteCurrencyCode()];
        return json_encode($result);
    }
}
