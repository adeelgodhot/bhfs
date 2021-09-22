define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'ko',
    'Amasty_RecurringPayments/js/model/update-estimation',
    'underscore'
], function (abstractTotal, quote, ko, updateEstimation, _) {
    'use strict';

    return abstractTotal.extend({
        defaults: {
            displayArea: 'after_details'
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Component} Chainable.
         */
        initObservable: function () {
            this._super();

            if (window.checkoutConfig.amastyRecurringConfig.isRecurringProducts) {
                updateEstimation.subscribeToCartChangesOnce()();
            }

            return this;
        },

        getValue: function (quoteItem) {
            return ko.computed(function () {
                var currentItem = _.find(quote.totals().items, function (item) {
                    return item.item_id === quoteItem.item_id && this.isVisible(item);
                }, this);

                if (currentItem) {
                    return this.getFormattedPrice(currentItem.extension_attributes.amasty_recurrent_estimate);
                }

                return '';
            }, this);
        },

        /**
         * @param {Object} quoteItem
         * @returns {boolean}
         */
        isVisible: function (quoteItem) {
            return Object.prototype.hasOwnProperty.call(quoteItem, 'extension_attributes')
                && Object.prototype.hasOwnProperty.call(
                    quoteItem.extension_attributes,
                    'amasty_recurrent_estimate'
                );
        }
    });
});
