
define([
    'jquery',
    'mage/translate',
    'mage/validation'
], function ($, $t) {
    'use strict';

    $.widget('mage.shippingCalculator', {
        options: {
            addToCartFormSelector: '#product_addtocart_form',
            resultContainerSelector: '.shipping-calculation-result-container',
            calculateButtonSelector: '.action.calculate',
            calculateButtonDisabledClass: 'disabled',
            calculateButtonTextWhileAdding: $t('Calculating...'),
            calculateButtonTextAdded: '',
            calculateButtonTextDefault: $t('Calculate')
        },

        /** @inheritdoc */
        _create: function () {
            this._bindSubmit();
        },

        /**
         * @private
         */
        _bindSubmit: function () {
            var self = this;
            this.element.on('submit', function (e) {
                var form = $(this),
                    addToCartForm = $(self.options.addToCartFormSelector);
                e.preventDefault();
                if (form.valid() && addToCartForm.valid()) {
                    self.submitForm(form);
                }
            });
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {jQuery} form
         */
        submitForm: function (form) {
            this.ajaxSubmit(form);
        },

        /**
         * @param {jQuery} form
         */
        ajaxSubmit: function (form) {
            var self = this;

            self.disableCalculateButton(form);

            $.ajax({
                url: form.attr('action'),
                data: form.serialize() + '&' + $(this.options.addToCartFormSelector).serialize(),
                type: 'post',
                /** @inheritdoc */
                success: function (res) {
                    self.enableCalculateButton(form);
                    self.element.find(self.options.resultContainerSelector).html(res);
                },
                /** @inheritdoc */
                error: function (res) {
                    self.enableCalculateButton(form);
                }
            });
        },

        /**
         * @param {String} form
         */
        disableCalculateButton: function (form) {
            var calculateButtonTextWhileAdding = this.options.calculateButtonTextWhileAdding,
                calculateButton = $(form).find(this.options.calculateButtonSelector);

            calculateButton.addClass(this.options.calculateButtonDisabledClass);
            calculateButton.find('span').text(calculateButtonTextWhileAdding);
            calculateButton.attr('title', calculateButtonTextWhileAdding);
        },

        /**
         * @param {String} form
         */
        enableCalculateButton: function (form) {
            var calculateButtonTextDefault = this.options.calculateButtonTextDefault,
                calculateButton = $(form).find(this.options.calculateButtonSelector);

            calculateButton.find('span').text(calculateButtonTextDefault);
            calculateButton.attr('title', calculateButtonTextDefault);
            calculateButton.removeClass(this.options.calculateButtonDisabledClass);
        }
    });

    return $.mage.shippingCalculator;
});
