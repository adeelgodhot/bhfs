/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'Magento_Ui/js/block-loader'
], function ($, _, ko, Component, blockLoader) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_RecurringPayments/view/customer/subscriptions',
            isLoading: ko.observable(false),
            visible: ko.observable(false),
            subscriptions: ko.observable([]),
            cancelUrl: '',
            loaderUrl: '',
            nextBillingDateTooltipEnabled: false,
            nextBillingDateTooltipText: ''
        },

        initialize: function () {
            this._super();

            if (this.loaderUrl) {
                blockLoader(this.loaderUrl);
            }

            this.visible(!!this.subscriptionsData.length);

            $.each(this.subscriptionsData, function (key, subscription) {
                subscription.detailsVisibility = ko.observable(false);
            });

            this.subscriptions(this.subscriptionsData);
        },

        toggleDetailsVisibility: function (subscription) {
            subscription.detailsVisibility(!subscription.detailsVisibility());
        },

        cancelClick: function (subscriptionInfo) {
            var confirmationPopup = $('[data-amrec-js="cancel-confirmation-popup"]');

            confirmationPopup.show();

            $('[data-amrec-js="close-confirmation"]').on('click', function () {
                confirmationPopup.hide();
            });

            $('[data-amrec-js="cancel-subscription"]').on('click', function () {
                var postData = {
                    subscription_id: subscriptionInfo.subscription.subscription_id,
                    subscription_payment: subscriptionInfo.subscription.payment_method,
                };

                confirmationPopup.hide();
                this.isLoading(true);

                $.ajax({
                    type: 'POST',
                    url: this.cancelUrl,
                    data: postData,
                    dataType: 'json'
                }).success(function (data) {
                    $.each(data, function (key, subscription) {
                        subscription.detailsVisibility = ko.observable(false);
                    });
                    this.subscriptions(data);
                    this.visible(data.length);
                }.bind(this))
                    .complete(function () {
                        this.isLoading(false);
                    }.bind(this));
            }.bind(this));
        }
    });
});
