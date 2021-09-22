define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'mage/template'
], function ($, priceUtils, mageTemplate) {
    'use strict';

    return function (updatePrices) {
        $.widget('mage.priceBox', updatePrices, {
            options: {
                percentDiscountTypeId: 'percent_product_price'
            },

            reloadPrice: function reDrawPrices() {
                this._super();

                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate),
                    basePrice = this.cache.displayPrices.basePrice,
                    feeData = $.extend({}, basePrice),
                    discountData = $.extend({}, basePrice);

                if (basePrice) {
                    this.setInitialFee(feeData, priceTemplate, priceFormat);
                    this.setDiscountAmount(discountData, priceTemplate, priceFormat);
                    $(document).trigger('changePriceTooltip.amrec', [ basePrice.formatted ]);
                }
            },

            setInitialFee: function (feeData, priceTemplate, priceFormat) {
                var subscriptionFeeElement = $('[data-amrec-js="initial-fee"]');

                if (!subscriptionFeeElement.length) {
                    return;
                }

                if (subscriptionFeeElement[0].dataset.feeType == this.options.percentDiscountTypeId) {
                    feeData.formatted = priceUtils.formatPrice(
                        feeData.amount * (+subscriptionFeeElement[0].dataset.feeAmount / 100),
                        priceFormat
                    );

                    $('[data-amrec-js="initial-fee"]').html(priceTemplate({
                        data: feeData
                    }));
                }
            },

            setDiscountAmount: function (discountData, priceTemplate, priceFormat) {
                var subscriptionDiscountElement = $('[data-amrec-js="discount-amount"]');

                if (!subscriptionDiscountElement.length) {
                    return;
                }

                if (subscriptionDiscountElement[0].dataset.discountType == this.options.percentDiscountTypeId) {
                    discountData.formatted = priceUtils.formatPrice(
                        discountData.amount * (+subscriptionDiscountElement[0].dataset.discountAmount / 100),
                        priceFormat);

                    subscriptionDiscountElement.html(priceTemplate({
                        data: discountData
                    }));
                }
            }
        });

        return $.mage.priceBox;
    }
});
