define([
    'jquery',
    'uiElement'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_RecurringPayments/product/recurring-payments/select',
            imports: {
                data: '${ $.provider }:plans'
            }
        },
        purchaseTypeSelector: '[data-amrec-js="purchase-type"]',
        recurringSettingsContainerSelector: '[data-amrec-js="recurring-settings"]',
        addToCartActionsSelector: '.box-tocart .actions > *:not(.tocart)',
        amazonActionsSelector: '.amazon-button-container',

        /**
         * Initializes Select component.
         *
         * @returns {Table} - Chainable.
         */
        initialize: function () {
            this._super();
            this.initHandlers();
            this.initPreSelect();

            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Table} - Chainable.
         */
        initObservable: function () {
            this._super()
                .observe([
                    'checkedValue',
                    'reloadPrice'
                ]);
            this.reloadPrice.extend({ notify: 'always' });

            return this;
        },


        /**
         * @returns {void}
         */
        initHandlers: function () {
            this.togglePurchaseType($(this.purchaseTypeSelector + ':checked').val() === 'subscribe');
            $(this.purchaseTypeSelector).on('change', function (event) {
                var isSubscribe = event.target.value === 'subscribe';

                this.togglePurchaseType(isSubscribe);
                this.reloadPrice(true);
            }.bind(this));
        },

        /**
         * Toggle purchase type
         *
         * @param {Boolean} isSubscribe
         * @returns {void}
         */
        togglePurchaseType: function (isSubscribe) {
            $(this.recurringSettingsContainerSelector).toggleClass('hidden', !isSubscribe);
            $(this.addToCartActionsSelector).toggle(!isSubscribe);
            $(this.amazonActionsSelector).toggle(!isSubscribe);
        },

        /**
         * Preselection plan
         *
         * @returns {void}
         */
        initPreSelect: function () {
            var firstOptionValue = this.data[0].plan_id;

            this.checkedValue(firstOptionValue);
        }
    });
});
