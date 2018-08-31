<?php
namespace Dibs\EasyCheckout\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Checkout
 * @package Dibs\EasyCheckout\Block
 */
class Cart extends Template
{
    protected $cart;
    protected $imageBuilder;
    protected $currency;
    public function __construct(Template\Context $context, array $data = array(),
                                \Magento\Checkout\Model\Cart $cart,
                                \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
                                \Magento\Directory\Model\Currency $currency
            ) {
                parent::__construct($context, $data);
                $this->cart = $cart;
                $this->imageBuilder = $imageBuilder;
                $this->currency = $currency;
    }
    
    public function getCartItems() {
        $items = $this->cart->getQuote()->getAllItems();
       
        return $items;
   }

     /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    public function getItemDisplayPriceExclTax($item)
    {
        if ($item instanceof QuoteItem) {
            return $item->getCalculationPrice();
        } else {
            return $item->getPrice();
        }
    }

    public function getCurrency() {
        return $this->currency;
    }

}
