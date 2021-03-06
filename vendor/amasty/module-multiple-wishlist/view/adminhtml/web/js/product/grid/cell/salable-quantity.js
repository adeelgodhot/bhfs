define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_MWishlist/product/grid/cell/salable-quantity.html'
        },

        /**
         * Get salable quantity data (stock name and salable qty)
         *
         * @param {Object} record - Record object
         * @returns {Array} Result array
         */
        getSalableQuantityData: function (record) {
            return record[this.index] ? record[this.index] : [];
        }
    });
});
