/**
 *  Amasty Title UI Component
 *
 *  @desc Page Title Component
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'amListName',
    'amWishlistSearch',
], function ($, Component, listName, amWishlistSearch) {
    'use strict';

    return Component.extend({
        defaults: {
            defaultWishlist: false,
            errors: [],
            backUrl: '',
            isNameValid: true,
            isOpened: true,
        },
        selectors: {
            socials: '[data-amwishlist-js="socials"]',
        },
        shareWindowOptions: 'width=600,height=600,scrollbars=no,resizable=no',

        /**
         * Initializes component
         */
        initialize: function () {
            this._super();

            this.hideDropdownEvent();
        },

        /**
         * Init Observes
         */
        initObservable: function () {
            var self = this;

            self._super().observe([
                'isEditNameActive',
                'listName',
                'itemsQty',
                'errors',
                'isNameValid',
                'isOpened'
            ]);

            self.defaultName = self.listName().trim();

            self.listName.subscribe(function (value) {
                var successAction = function () {
                        self.errors(false);
                        self.isNameValid(true);
                    },
                    errorAction = function (response) {
                        self.errors(response.errors);
                        self.isNameValid(false);
                    };

                if (value && self.defaultName !== value.trim()) {
                    listName.validate(value, successAction, errorAction);
                }
            }, self);

            return self
        },

        /**
         * Overlay click handler
         */
        clickOverlay: function () {
            if (!this.listName().length) {
                this.listName(this.defaultName);
                this.isEditNameActive(false);
            } else if (!this.errors().length) {
                this.isEditNameActive(false);
            }
        },

        isDeleteable: function () {
            return !this.defaultWishlist;
        },

        isSocials: function () {
            return this.facebook || this.twitter;
        },

        toggleSocials: function (event) {
            event.preventDefault();

            this.isOpened(!this.isOpened());
        },

        openShareWindow: function (event, socialUrl) {
            event.preventDefault();

            if (event.type === 'readystatechange') {
                return;
            }

            window.open(socialUrl, 'popup', this.shareWindowOptions);
        },

        hideDropdownEvent: function () {
            var self = this;

            $(window).on('click', function (event) {
                if (amWishlistSearch().isOutsideClick(event, $(self.selectors.socials).children())) {
                    self.isOpened(false);
                }
            });
        }
    });
});
