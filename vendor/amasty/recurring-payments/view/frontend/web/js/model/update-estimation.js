/**
 * Model for update next billing payment of subscription product
 */
define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/totals',
    'underscore'
], function (
    ko,
    quote,
    storage,
    errorProcessor,
    payloadExtender,
    urlBuilder,
    totals,
    _
) {
    'use strict';

    return {
        /**
         * @returns {Object}
         */
        getPayload: function () {
            var payload,
                shippingMethod = quote.shippingMethod();

            payload = {
                addressInformation: {
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': shippingMethod ? shippingMethod['method_code'] : '',
                    'shipping_carrier_code': shippingMethod ? shippingMethod['carrier_code'] : ''
                }
            };

            payloadExtender(payload);

            return payload;
        },

        /**
         * Returns once function for subscribe to cart changes
         * @returns {*}
         */
        subscribeToCartChangesOnce: function () {
            return _.once(function () {
                quote.shippingAddress.subscribe(this.updateEstimation, this);
                quote.shippingMethod.subscribe(this.updateEstimation, this);
            }.bind(this));
        },

        /**
         * @param {Array} estimationItems
         * @returns {void}
         */
        updateQuoteItems: function (estimationItems) {
            var quoteTotals = quote.totals();

            quoteTotals.items.forEach(function (item, index) {
                estimationItems.forEach(function (estimateItem) {
                    if (estimateItem.item_id === +item.item_id) {
                        quoteTotals.items[index].extension_attributes.amasty_recurrent_estimate = estimateItem.value;
                    }
                });
            });

            quote.totals(quoteTotals);
        },

        /**
         * @returns {jQuery.Deferred}
         */
        updateEstimation: function () {
            var payload = this.getPayload();

            totals.isLoading(true);

            return storage.post(
                urlBuilder.createUrl('/amasty-recurring-payments/estimate-by-shipping-information', {}),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    this.updateQuoteItems(response);
                    totals.isLoading(false);
                }.bind(this)
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    totals.isLoading(false);
                }
            );
        }
    };
});
