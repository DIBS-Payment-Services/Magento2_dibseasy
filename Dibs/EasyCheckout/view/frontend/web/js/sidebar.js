/**
  * Copyright © Magento, Inc. All rights reserved.
  * See COPYING.txt for license details.
  */

define([
     'jquery',
     'Dibs_EasyCheckout/js/checkout',
      'Magento_Customer/js/customer-data'
], function ($, checkout, customerData) {
     'use strict';
     return function(originalWidget) {
       $.widget('mage.sidebar', $['mage']['sidebar'],
       {
         _updateItemQtyAfter: function (elem) {
             this._hideItemButton(elem);
             var itemId = elem.data('cart-item');
             
         var current_url = window.location.href; 
         if(current_url.includes('dibs_easy/checkout/start')) {
             console.log(BASE_URL);
             window.location.href = BASE_URL + 'dibs_easy/checkout/start';
         }
       }, 
 
       _removeItemAfter: function (elem) {
            var productData = customerData.get('cart')().items.find(function (item) {
                return Number(elem.data('cart-item')) === Number(item['item_id']);
            });

            $(document).trigger('ajax:removeFromCart', productData['product_sku']);
             var current_url = window.location.href; 
           if(current_url.includes('dibs_easy/checkout/start')) {
             console.log(BASE_URL);
             window.location.href = BASE_URL + 'dibs_easy/checkout/start';
            }
        }
       
       });
        return originalWidget;
     };
});