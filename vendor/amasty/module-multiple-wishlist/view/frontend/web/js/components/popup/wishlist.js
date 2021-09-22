/**
 *  Amasty Wishlist for Popup UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'rjsResolver',
    'Magento_Customer/js/customer-data',
    'amListName',
    'uiRegistry',
], function ($, ko, Component, resolver, customerData, listName, registry) {
    'use strict';

    var wishlistSection = customerData.get('mwishlist');

    return Component.extend({
        defaults: {
            validateTimeOut: 1000,
            selectors: {
                formKeyInputSelector: 'input[name="form_key"]',
            },
            actions: {
                addNewList: '/mwishlist/wishlist/create',
                validateNewName: '/mwishlist/wishlist/validateWishlistName'
            },
            modules: {
                popup: 'ampopup'
            },
            typesMap: [],
            excludeIds: [],
            isNameValid: false,
            currentListType: 0,
            tabs: wishlistSection()['wishlist_list']
        },

        /**
         * Clearing state for new list area
         */
        clearNewList: function () {
            this.newListActive(false);
            this.newListName(null);
            this.newNameErrors(false);
            this.isNameValid(true);
        },

        /**
         * Init Observes
         */
        initObservable: function () {
            var self = this;

            this._super().observe([
                'currentListType',
                'currentListId',
                'newListActive',
                'newListName',
                'newNameErrors',
                'isNameValid',
                'tabs',
                'excludeIds'
            ]);

            resolver(function () {
                self.newListName.extend({
                    rateLimit: {
                        method: "notifyWhenChangesStop", timeout: self.validateTimeOut
                    }
                });

                self.popup().isActive.subscribe(function (value) {
                    if (!value) {
                        self.clearNewList();
                    }
                });
            });

            wishlistSection.subscribe(function (value) {
                self.tabs(value['wishlist_list']);
            }, self);

            self.newListName.subscribe(function (value) {
                var successAction = function () {
                        self.newNameErrors(false);
                        self.isNameValid(true);
                    },
                    errorAction = function (response) {
                        self.newNameErrors(response.errors);
                        self.isNameValid(false);
                    };

                if (value) {
                    listName.validate(value, successAction, errorAction);
                }
            }, self);

            return self
        },

        /**
         * Adding new wishlist with new list name
         */
        addNewList: function () {
            var self = this,
                data = {
                    'wishlist[name]': self.newListName(),
                    'wishlist[type]': self.currentListType(),
                },
                successAction = function () {
                    self.clearNewList();
                };

            listName.ajaxAction(this.actions.addNewList, data, successAction);
        },

        /**
         * Bundle State for wishlist visibility
         *
         * @param {number} wishlist_id - id of wishlist
         * @returns {boolean}
         */
        isWishlistVisible: function (wishlist_id) {
            return this.excludeIds().indexOf(wishlist_id) === -1;
        },

        /**
         * Take label for list type
         *
         * @param {number} index - url to controller
         * @returns {string}
         */
        getTypeLabel: function (index) {
            return this.typesMap[index];
        }
    });
});
