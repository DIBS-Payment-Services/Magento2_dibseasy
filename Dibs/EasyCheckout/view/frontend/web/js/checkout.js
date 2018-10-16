define(['uiComponent', 
        'jquery', 
        'ko',
        'Magento_Ui/js/modal/confirm',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url',
        'mage/storage',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Ui/js/modal/alert'
       ], function(Component, $, ko, confirm, fullScreenLoader, url, storage, cache ,alert) {
        var loader = fullScreenLoader;
        var alert = alert; 
        return Component.extend({
           shipping: ko.observable(''),
           subtotal: ko.observable(''),
           grand_total: ko.observable(''),
           currency: ko.observable(''),
           shippingMethods: ko.observableArray([]),
           cartTotals: ko.observableArray([]),
           cartProducts: ko.observableArray([]),
           checkoutUrl: '',
           
           initialize: function () {
              this._super();
              var action = '';
               var checkoutOptions = {
                    checkoutKey: this.checkout.checkoutKey,
                    paymentId : this.checkout.paymentId,
                    containerId : this.checkout.containerId + '',
                    language: this.checkout.language
                };
                this.checkoutinit = new Dibs.Checkout(checkoutOptions);
                var ct = this;
                this.checkoutinit.on('address-changed', function(address) {
                   ct.updateView({"action" : "change_address"});
                });
                this.updateView({"action" : "start"});
                this.checkoutUrl = this.checkout.checkout_url;
            },
            
           
           
           updateView: function(params) {
             action = params.action;
             context = this;
             this.checkoutinit.freezeCheckout();
             loader.startLoader();
        
             $.post(this.checkout.updateview_url , params ,function() {
             }).done(function(result) {
                var parsed = JSON.parse(result);
                    
                    if(parsed.exception) {
                        loader.stopLoader();
                        alert({
                            title: 'Alert',
                            content: parsed.exception,
                            actions: {
                                always: function(){
                                   window.location.href = context.checkout.cart_url;
                                }
                            }
                        });
                        
                    }
                    
                    if(parsed.redirect) {
                        window.location.href = context.checkout.cart_url;
                    }
                    
                    if(parsed.shipping.result === 'error' && 
                            parsed.shipping.error.type === 'no_methods' 
                            && action === 'change_address') {
                        loader.stopLoader();
                        alert({
                            title: 'Alert',
                            content: parsed.shipping.error.message,
                            actions: {
                                always: function(){
                                   window.location.href = context.checkout.cart_url;
                               }
                            }
                        });
                   }
                
                     var arr = [];
                     $.each(parsed.shipping.methods, function( index, value){
                        arr.push({title: value.method_title, price: value.price, code: value.code, active: value.active});
                     });
           
                   context.shippingMethods(arr);
                   
                   context.cartTotals(parsed.totals);
            
                   if(parsed.cart_items.length > 0) {
                       context.cartProducts(parsed.cart_items);
                   }
                   context.checkoutinit.thawCheckout();
                   loader.stopLoader();

             });
             
             
           },
           
          shippingClick: function(item, event) {
                context = this;  
                $(".dibs-easy-shipping-selector").each(function() {
                        $(this).addClass("dibs-easy-non-active");
                });
                $(event.target).removeClass("dibs-easy-non-active");
                $(event.target).addClass("dibs-easy-active");
                this.updateView({"action":"change_shipping", "method": $(event.target).attr("id")}); 
           },
           
           cartProductInputFocusin : function(item, event) {
                $(event.target).data('val', $(event.target).val());
           },
           
           cartProductInputFocusout : function(item, event) {
                this.validateQty($(event.target));
           },
           
           cartProductRemove : function(item, event) {
                console.log("cartProductRemove");
                var id = $(event.target).attr("id");
                var ct = this;
                confirm({
                    content: 'Delete this item ?',
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                           ct.updateView({"action": "remove_item", "id": id});
                        },
                        /** @inheritdoc */
                        always: function (e) {
                      }
                    }
                   });
           },
      
           validateQty: function (elem) {
            var itemQty = elem.data('val');

              if (!this.isValidQty(itemQty, elem.val())) {
                elem.val(itemQty);
              } else {
                  this.updateView({"action":"update_qty", "id": elem.attr('id'), "qty": elem.val()});
              }
            },
            
           isValidQty: function (origin, changed) {
            return origin != changed && //eslint-disable-line eqeqeq
                changed.length > 0 &&
                changed - 0 == changed && //eslint-disable-line eqeqeq
                changed - 0 > 0;
            }

    });
});