define(['uiComponent', 
        'jquery', 
        'ko',
        'Magento_Ui/js/modal/confirm',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'mage/storage',
        'Magento_Checkout/js/model/cart/cache',
       ], function(Component, $, ko, confirm, fullScreenLoader, url, storage, cartCache) {

        return Component.extend({
           shipping: ko.observable(''),
           subtotal: ko.observable(''),
           grand_total: ko.observable(''),
           currency: ko.observable(''),
           shippingMethods: ko.observableArray([]),
           initialize: function () {
              this._super();
               var checkoutOptions = {

                    checkoutKey: this.checkout.checkoutKey,
                    paymentId : this.checkout.paymentId,
                    containerId : this.checkout.containerId + '',
                    language: this.checkout.language
                };
                this.checkoutinit = new Dibs.Checkout(checkoutOptions);
   
              var ct = this;
              this.checkoutinit.on('address-changed', function(address) {
                var countryCode = address.countryCode;
       
                switch (countryCode) {
                    case 'SWE':
                        countryCode = 'SE';
                    break;
                    case 'NOR':
                         countryCode = 'NO';
                    break;
                    case 'DNK':
                        countryCode = 'DK';
                    break;
                default:
                    //countryCode = 'SE';
                 }
                 
                  var shippingMethodfromCart = '';
                 
                  ct.getShippingMethods(shippingMethodfromCart);
                  //ct.getTotals();
               });
               $(".dibs-easy-remove-link-a").click(function(){
                var id = $(this).attr("id");
                confirm({
                    content: 'Delete this item ?',
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                           fullScreenLoader.startLoader();
                           $.post(url.build('/dibs_easy/checkout/updatecart') , {"remove_item_id": id} ,function(data) {
                                window.location.href = url.build('/dibs_easy/checkout/start');
                           });
                        },
                        /** @inheritdoc */
                        always: function (e) {
                      }
                    }
                   });
                });
                
               $('#top-cart-btn-checkout').click(function(){
                  alert(100500); 
               });
                
            },

           getTotals: function() {
               return [];
           },
           
           checkoutUrl: url.build('/checkout'),

           getTotals: function() {
                context = this;
                this.checkoutinit.freezeCheckout();
                $.get(url.build('/dibs_easy/checkout/totals') , function(data) { 
                  }).done(function(data) {
                      var parsed = JSON.parse(data);
                      context.shipping(parsed.shipping);
                      context.subtotal(parsed.subtotal);
                      context.grand_total(parsed.currency + parsed.grand_total);
                      context.checkoutinit.thawCheckout();
                });
           },

           getShippingMethods: function(cartShippingMethod) {
             context = this;  
              this.checkoutinit.freezeCheckout();
             $.post(url.build('/dibs_easy/checkout/shipping'), {"shippingMethod" : cartShippingMethod} ,function(){
             
             }).done(function(result) {
                   var parsed = JSON.parse(result);
                   var arr = [];
                   $.each(parsed, function( index, value){
                       arr.push({title: value.method_title, price: value.price, code: value.code, active: value.active});
                   });
                   
                   /*
                   if(arr.length == 1) {
                       var method = arr[0];
                       arr = [];
                       arr.push({title: method.title, price: method.price, 
                                 code: method.code, active: 1});
                             
                       context.setShippingMethod(method.code);
                   }
                   */
                   
                   context.shippingMethods(arr);
                   context.checkoutinit.thawCheckout();
                   context.getTotals();
            });
           },

           setShippingMethod: function(shippingCode) {
                 context = this;
                 $.post(url.build('/dibs_easy/checkout/updatecart'),
                    {"shipping_method": shippingCode}, function(data) {
                }).done(function() {
                    context.getTotals();
                });
           },
           
          shippingClick: function(item, event) {
                context = this;  
                $(".dibs-easy-shipping-selector").each(function() {
                        $(this).addClass("dibs-easy-non-active");
                });
                $(event.target).removeClass("dibs-easy-non-active");
                $(event.target).addClass("dibs-easy-active");
                this.checkoutinit.freezeCheckout();
                this.setShippingMethod($(event.target).attr("id"));
           },

    });
});