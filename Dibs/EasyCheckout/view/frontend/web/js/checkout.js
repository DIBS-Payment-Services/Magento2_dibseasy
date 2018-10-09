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
                
               $( ".dibs-easy-qty-input" ).change(function() {
                    var prev = $(this).data('val');
                    var current = $(this).val();
                    
                    if(current > 0 && current !== prev) {
                        ct.updateCartQty();
                    }
                    
                    console.log("Prev value " + prev);
                    console.log("New value " + current);
               });
               
               $( ".dibs-easy-qty-input" ).on('focusin', function() {
                     $(this).data('val', $(this).val());
               });
               
               $( ".dibs-easy-qty-input" ).on('focusout', function() {
                     ct.validateQty($(this));
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
           
           updateCartQty: function() {
               console.log("updateCartQty");
           },
           
           validateQty: function (elem) {
            var itemQty = elem.data('val');

              if (!this.isValidQty(itemQty, elem.val())) {
                elem.val(itemQty);
              } else {
                  fullScreenLoader.startLoader();
                  $.post(url.build('/dibs_easy/checkout/updatecart'), 
                                   {"item_qty":elem.val(), 
                                    "item_id":elem.attr('id')} ,
                  function(){
                    
                  }).done(function(result) {
                      window.location.reload();
                  });
              }
            },
            
           isValidQty: function (origin, changed) {
            return origin != changed && //eslint-disable-line eqeqeq
                changed.length > 0 &&
                changed - 0 == changed && //eslint-disable-line eqeqeq
                changed - 0 > 0;
            },


    });
});