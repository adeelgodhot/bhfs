/*
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

define([
    'jquery',
    'mage/backend/validation',
    'jquery/validate'
], function ($) {
    'use strict';

    $.widget('mage.amThankYouPageSubscribeToNewsletter', {
        options: {
            successContainerSelector: ''
        },

        _create: function () {
            this.form = this.element;
            this.successContainer = $(this.options.successContainerSelector);

            this._initSubmitEvent();
        },

        _initSubmitEvent: function () {
            this.form.on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (this.form.validation('isValid')) {
                    $.ajax({
                        type: 'post',
                        url: this.form.attr('action'),
                        data: this.form.serialize(),
                        cache: false,
                        showLoader: 'true'
                    }).always(this._showSuccess.bind(this));
                }

                return false;
            }.bind(this));
        },

        _showSuccess(response) {
            this.form.hide();
            this.successContainer.show();
            if (response.message) {
                this.successContainer.text(response.message);
            }
        }
    });

    return $.mage.amThankYouPageSubscribeToNewsletter;
});
