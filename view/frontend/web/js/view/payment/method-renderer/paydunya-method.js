/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url'
    ],
    function (
              Component,
              placeOrderAction,
              selectPaymentMethodAction,
              customerData,
              checkoutData,
              additionalValidators,
              url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Paydunya_PaydunyaMagento/payment/paydunya'
            },

            placeOrder: function () {
                if (event) {
                    event.preventDefault();
                }


                var self = this,
                    placeOrder;

                    this.isPlaceOrderActionAllowed(false);

                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                    jQuery.when(placeOrder).then(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;

            },
            payWithPaydunya: function(){
                if (event) {
                    event.preventDefault();
                }


                var self = this,
                    placeOrder;

                this.isPlaceOrderActionAllowed(false);

                placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

                jQuery.when(placeOrder).then(function () {
                    self.isPlaceOrderActionAllowed(true);
                }).done(
                    // this.afterPlaceOrder.bind(this)
                );
                var $ = jQuery.noConflict();
                $.ajax({
                    type: "GET",
                    url: window.location.host+"/paydunyamagento/payment/index",
                    success: function(data)
                    {
                        console.log(data); // show response from the php script.
                    }
                });
                PayDunya.setup({
                    selector: $(event.target),
                    url: window.location.host+"/paydunyamagento/payment/api",
                    method: "GET",
                    displayMode: PayDunya.DISPLAY_IN_POPUP,
                    beforeRequest: function() {
                        console.log("About to get a token and the url");
                    },
                    onSuccess: function(token) {
                        console.log("Token: " +  token);
                    },
                    onTerminate: function(ref, token, status) {
                        alert("le paiement a été effectué avec succès")

                        console.log(ref);
                        console.log(token);
                        console.log(status);
                    },
                    onError: function (error) {
                        alert("Unknown Error ==> ", error.toString());
                    },
                    onUnsuccessfulResponse: function (jsonResponse) {
                        console.log("Unsuccessful response ==> " + jsonResponse);
                    },
                    onClose: function() {
                        document.querySelectorAll('.vbox-overlay').forEach(function(a){
                            a.remove()
                        })
                        console.log("Close");
                    }
                }).requestToken();
            },
            afterPlaceOrder: function () {
                console.log('orderplaced');
                window.location.replace(url.build('paydunyamagento/payment/index'));
                console.log('redirected');
            },/** Returns send check to info */

            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },

            getDisplayTitle: function() {
                return window.checkoutConfig.payment.paydunyamagento.display_title;
            },

            getDisplayDescription: function() {
                return window.checkoutConfig.payment.paydunyamagento.display_description;
            }
        });
    }
);
