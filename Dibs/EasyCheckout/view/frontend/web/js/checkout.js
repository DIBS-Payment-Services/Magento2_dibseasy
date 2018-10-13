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
           cartTotals: ko.observableArray([]),
           
           
           initialize: function () {
              this._super();
               var checkoutOptions = {

                    checkoutKey: this.checkout.checkoutKey,
                    paymentId : this.checkout.paymentId,
                    containerId : this.checkout.containerId + '',
                    language: this.checkout.language
                };
                this.checkoutinit = new Dibs.Checkout(checkoutOptions);
                this.getTotals();
           
                var ct = this;
                this.checkoutinit.on('address-changed', function(address) {
                  var shippingMethodfromCart = '';
                  ct.getShippingMethods(shippingMethodfromCart);
                });
               
               
               $(".dibs-easy-remove-link-a").click(function(){
                var id = $(this).attr("id");
                var ct = this;
                confirm({
                    content: 'Delete this item ?',
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                           removeProduct(id);
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
               
               function removeProduct(id) {
                   $.post(url.build('/dibs_easy/checkout/updatecart') , {"remove_item_id": id} ,function(data) {
                        ct.getShippingMethods('');
                   });
               }
               
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
                      
                      console.log(parsed);
                      /*
                      context.shipping(parsed.shipping);
                      context.subtotal(parsed.subtotal);
                      context.grand_total(parsed.currency + parsed.grand_total);
                      context.checkoutinit.thawCheckout();
                      */
                     context.checkoutinit.thawCheckout();
                      context.cartTotals(parsed);
                });
           },
           
           removeProduct: function(id) {
             $.post(url.build('/dibs_easy/checkout/updatecart') , {"remove_item_id": id} ,function(data) {
                window.location.href = url.build('/dibs_easy/checkout/start');
             });
                     
           },

           getShippingMethods: function(cartShippingMethod) {
             context = this;  
              this.checkoutinit.freezeCheckout();
             $.post(url.build('dibs_easy/checkout/shipping'), {"shippingMethod" : cartShippingMethod} ,function(){
             
             }).done(function(result) {
                   var parsed = JSON.parse(result);
                   var arr = [];
                   console.log(parsed);
                   
                   if(parsed.result === 'success') {
                        $.each(parsed.methods, function( index, value){
                            arr.push({title: value.method_title, price: value.price, code: value.code, active: value.active});
                        });
                        context.shippingMethods(arr);
                        context.checkoutinit.thawCheckout();
                        context.getTotals();
                   } else {
                       alert(parsed.message);
                       window.location.href = url.build('checkout/cart');
                   }
                   
                   
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
                      
                      fullScreenLoader.stopLoader();
                      //window.location.reload();
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