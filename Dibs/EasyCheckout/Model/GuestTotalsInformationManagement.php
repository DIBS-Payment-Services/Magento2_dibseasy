<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dibs\EasyCheckout\Model;

class GuestTotalsInformationManagement implements \Magento\Checkout\Api\GuestTotalsInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Checkout\Api\TotalsInformationManagementInterface
     */
    protected $totalsInformationManagement;
    
    protected $checkoutSession;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Checkout\Api\TotalsInformationManagementInterface $totalsInformationManagement,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->totalsInformationManagement = $totalsInformationManagement;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        
        $this->checkoutSession->setCartShippingCarrierCode($addressInformation->getShippingCarrierCode());
        $this->checkoutSession->setCartShippingMethodCode($addressInformation->getShippingMethodCode());
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->totalsInformationManagement->calculate(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }
}