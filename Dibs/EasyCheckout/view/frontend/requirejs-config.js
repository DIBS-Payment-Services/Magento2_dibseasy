var config = {
    urlArgs: "bust=" + (new Date()).getTime(), // Disable require js cache
     
    config: {
         mixins: {
             'Magento_Checkout/js/sidebar': {
                 'Dibs_EasyCheckout/js/sidebar': true
             },
         }
     },

};