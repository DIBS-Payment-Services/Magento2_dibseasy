define(['uiComponent', 
        'jquery', 
        'ko',
        'Magento_Ui/js/modal/confirm',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/url'
       ], function(Component, $, ko, confirm, fullScreenLoader, url) {

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
                  ct.getShippingMethods(countryCode);
                  ct.getTotals();
               });
                this.getTotals();
                console.log(checkoutOptions);

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
            },

           incrementClickCounter: function() {
             console.log(this.checkout);
           },

           setSipping: function() {
            
           },

           getTotals: function() {
               return [];
           },

           getTotals: function() {
                context = this;
                this.checkoutinit.freezeCheckout();
                $.get(url.build('/dibs_easy/checkout/totals') , function(data) { 
                  }).done(function(data) {
                      var parsed = JSON.parse(data);
                      console.log(parsed);
                      context.shipping(parsed.shipping);
                      context.subtotal(parsed.subtotal);
                      context.grand_total(parsed.currency + parsed.grand_total);
                      context.checkoutinit.thawCheckout();
                });
           },

           getShippingMethods: function(country) {
             context = this;  
              this.checkoutinit.freezeCheckout();
             $.post(url.build('/dibs_easy/checkout/shipping') ,{countrycode: country}, function(){
             
             }).done(function(result) {
                   var parsed = JSON.parse(result);
                   var arr = [];
                   $.each(parsed, function( index, value){
                       arr.push({title: value.carrier_title, price: value.price, code: value.code, clas: value.class});
                   });
                   
                   if(arr.lenth == 1) {
                       console.log(arr);
                   }
                       
                   context.shippingMethods(arr);
                   context.checkoutinit.thawCheckout();
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

           setShippingMethod: function(shippingCode) {
                 context = this;
                 $.post(url.build('/dibs_easy/checkout/updatecart'),
                    {"shipping_method": shippingCode}, function(data) {
                }).done(function() {
                    context.getTotals();
                });
           },

    });
});