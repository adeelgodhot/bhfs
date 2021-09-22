define([
    'ko',
    'jquery',
    'uiComponent',
    'mage/storage',
    'Amasty_ExitPopup/js/model/url-builder',
    'mage/validation'
], function (ko, $, Component, storage, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ExitPopup/form/popup',
            isFormPopUpVisible: false,
            isButtonEnabled: false,
            customerEmail: '',
        },

        initialize: function () {
            this._super();

            $(document).on('mouseleave', function () {
                if (this.getExpirationState() && event.clientY <= 5 && $('[data-amexit-js="exit-popup"]').is(':hidden')) {
                    var dataForm = $('[data-amexit-js="email-form"]');

                    this.togglePopup(true);
                    this.onOverlayClick();
                    this.setExpirationDate();

                    dataForm.mage('validation', {
                        errorPlacement: function(error) {
                            $('[data-amexit-js="error-container"]').html(error);
                        },
                    });
                }
            }.bind(this));

            return this;
        },

        initObservable: function() {
            this._super()
                .observe(['isFormPopUpVisible', 'isButtonEnabled', 'customerEmail']);

            return this;
        },

        isCheckboxRender: function () {
            return this.popup.ask;
        },

        getConsentMessage: function () {
            return this.popup.consent;
        },

        togglePopup: function (state) {
            this.isFormPopUpVisible(state);
        },

        getTitle: function () {
            return this.popup.title;
        },

        getText: function () {
            return this.popup.text;
        },

        sendEmail: function () {
            var serviceUrl = urlBuilder.createUrl('/ampopup/sendEmail', this.popup.storeCode + '/');

            if($('[data-amexit-js="email-form"]').validation('isValid')) {
                $('[data-amexit-js="error-container"]').html('');
                this.isFormPopUpVisible(false);

                return storage.post(
                    serviceUrl,
                    JSON.stringify({email: this.customerEmail()}),
                    false
                );

            }
        },

        onOverlayClick: function() {
            $('[data-amexit-js=exit-popup]').on('click', function () {
                if ($(event.target).is('[data-amexit-js=exit-popup]')) {
                    this.togglePopup(false);
                }
            }.bind(this));
        },

        setExpirationDate: function () {
            var currentTime = new Date();

            localStorage.setItem('amExitPopupExpirationDate', currentTime.setSeconds(currentTime.getSeconds() + this.popup.delayInSeconds));
        },

        getExpirationState: function () {
          return localStorage.getItem('amExitPopupExpirationDate') < new Date()
        }
    });
});
