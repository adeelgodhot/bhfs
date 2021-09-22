define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
    "use strict";
    return Component.extend({

        /**
         * @returns {Boolean}
         */
        isDisplayed: function () {
            return !!totals.getSegment('amasty_recurring_payments_fee');
        },

        /**
         * @returns {String}
         */
        getFeeLabel: function () {
            return totals.getSegment('amasty_recurring_payments_fee').title;
        },

        /**
         * Get formatted price
         * @returns {*|String}
         */
        getValue: function () {
            var price = totals.getSegment('amasty_recurring_payments_fee').value;

            return this.getFormattedPrice(price);
        },
    });
});
