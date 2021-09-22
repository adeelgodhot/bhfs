/**
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

    $.widget('mage.amThankYouPageCreateAccountForm', {
        options: {
            successContainerSelector: '',
            errorContainerSelector: ''
        },

        _create: function () {
            this.form = this.element;
            this.successContainer = $(this.options.successContainerSelector);
            this.errorContainer = $(this.options.errorContainerSelector);

            this._initSubmitEvent();
        },

        _initSubmitEvent: function () {
            this.form.on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.errorContainer.hide();

                if (this.form.validation('isValid')) {
                    $.ajax({
                        type: 'post',
                        url: this.form.attr('action'),
                        data: this.form.serialize(),
                        cache: false,
                        showLoader: 'true'
                    })
                        .fail(this._showError.bind(this, 'Error occurred'))
                        .done(function (response) {
                            if (response.errors) {
                                this._showError(response.message);
                            } else {
                                this._showSuccess(response.message);
                            }
                        }.bind(this));
                }

                return false;
            }.bind(this));
        },

        _showSuccess(message) {
            this.successContainer.html(message);
            this.successContainer.show();
            this.form.hide();
        },

        _showError(message) {
            this.errorContainer.html(message);
            this.errorContainer.show();
        }
    });

    return $.mage.amThankYouPageCreateAccountForm;
});
