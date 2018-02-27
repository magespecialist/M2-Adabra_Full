/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2014 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data'
], function(Component, $, customer) {

    return Component.extend({
        initialize: function(){
            this._super();

            this.getTrackingParams().done(function(params) {
                this.sendTracking(params);
            }.bind(this));

            customer.reload(['cart', 'adabra'], false)
        },

        getTrackingParams: function() {
            var me = this;
            var promise = $.Deferred();

            var params = [];

            var solvable = {
                user: false,
                actions: false
            };

            var resolve = function(type) {
                solvable[type] = true;

                for(var key in solvable) {
                    if(solvable.hasOwnProperty(key)) {
                        if(solvable[key] === false) {
                            return false;
                        }
                    }
                }

                promise.resolve(params);
            };

            params.push(["setDocumentTitle", document.title ]);
            params.push(["setLanguage", this.locale ]);
            params.push(["setSiteId",this.siteId]);
            params.push(["setCatalogId",this.catalogId]);
            params.push(["setPageType", this.pageType]);
            params.push(['trkPageView']);

            var skus = [];
            var quantities = [];

            this.getCustomer().done(function(customerInfo) {
                if (customerInfo.logged === true) {
                    params.push(["setSiteUserId", customerInfo.id]);
                }

                for (i in customerInfo.cart) {
                    if (customerInfo.cart.hasOwnProperty(i)) {
                        skus.push(customerInfo.cart[i].sku);
                        quantities.push(customerInfo.cart[i].qty);
                    }
                }

                params.push(['setCtxParamProductIds'].concat(skus));
                params.push(['setCtxParamProductQuantities'].concat(quantities));

                var actions = me.getActions();
                for (var i in actions) {
                    if (actions.hasOwnProperty(i)) {
                        var action = actions[i];
                        var final = [action.action];

                        if (action.params) {
                            for (var ii in action.params) {
                                if (action.params.hasOwnProperty(ii)) {
                                    final.push(action.params[ii])
                                }
                            }
                        }
                        params.push(final);
                    }
                }

                promise.resolve(params);
            });

            return promise;
        },

        sendTracking: function(params) {
            var me = this;

            if(me.trackingUrl) {
                var protocol = ("https:" == document.location.protocol) ? "https" : "http";
                var url = protocol + "://" + me.trackingUrl + "/";

                params.push(["setTrackerUrl", url]);
                window._sbnaq = window._sbnaq.concat(params);


                // console.log(window._sbnaq); for debug
                //
                // var script = $("<script></script>");
                // script.attr('src', url + "sbn.js");
                // script.appendTo('head');

                require([url + "sbn.js"]);

            }
        },

        getActions: function() {
            var actions = $.mage.cookies.get('adabra_actions');

            if (actions) {
                this.delCookie();
                return JSON.parse(actions);
            }

            return [];
        },

        getCustomer: function() {
            var solvable = {
                info: false,
                cart: false
            };

            var promise = $.Deferred();
            var result = {};

            var resolve = function(type) {
                solvable[type] = true;
                var canResolve = true;

                for(var i in solvable) {
                    if(solvable.hasOwnProperty(i)) {
                        if (!solvable[i]) {
                            canResolve = false;
                        }
                    }
                }

                if (canResolve) {
                    promise.resolve(result);
                }
            };

            var customerData = customer.get('adabra');
            var cart = customer.get('cart');

            this.getCustomerInfo(customerData, result, resolve);

            customerData.subscribe(function() {
                this.getCustomerInfo(customerData, result, resolve);
            }.bind(this));

            this.getCartData(cart, result, resolve);

            cart.subscribe(function() {
                this.getCartData(cart, result, resolve)
            }.bind(this));


            return promise;
        },

        getCustomerInfo: function(info, result, callback) {
            if(Object.keys(info()).length) {
                result.logged = info().logged;
                result.id = info().id;

                callback('info');
            }
        },

        getCartData: function(cart, result, callback) {
            if (Object.keys(cart()).length) {
                var resultCart = [];

                for(var i in cart().items) {
                    if(cart().items.hasOwnProperty(i)) {
                        var item = cart().items[i];
                        resultCart.push(
                            {
                                sku: item.product_sku,
                                qty: item.qty
                            }
                        );
                    }
                }

                result.cart = resultCart;

                callback('cart')
            }

        },

        delCookie: function() {
            var date = new Date(0);
            var cookie = 'adabra_actions' + "=" + "; path=/; expires=" + date.toUTCString();
            document.cookie = cookie;
        }

    });

});
