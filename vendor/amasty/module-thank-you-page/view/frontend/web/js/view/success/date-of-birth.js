define([
    'Magento_Ui/js/form/element/date',
    'jquery',
    'Amasty_ThankYouPage/js/view/success/datepicker'
], function (Component, $) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Amasty_ThankYouPage/form/date',
        },

        /**
         * initialize
         */
        initialize: function () {
            this._super();
            this.options.dateFormat = 'dd/mm/yy';
            this.options.maxDate = '-1d';
            this.options.changeMonth = true;
            this.options.changeYear = true;
            this.options.yearRange = "-120y:c+nn";
            this.options.showButtonPanel = true;
            this.options.showOn = 'both';
        },

        isRequired: function () {
            return this.isRequiredComponent;
        },
    });
});
