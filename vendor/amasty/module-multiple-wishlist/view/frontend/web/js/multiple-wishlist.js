define([
    'jquery',
    'mage/template',
    'Magento_Customer/js/customer-data',
    'Amasty_MWishlist/js/action/reload-blocks',
    'Amasty_MWishlist/js/action/reload-components',
    'uiRegistry',
    'rjsResolver',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'mage/cookies'
], function ($, mageTemplate, customerData, reloadBlocks, reloadComponents, registry, resolver, $t, confirm) {
    'use strict';

    $.widget('mage.amMultipleWishlist', {
        options: {
            selectors: {
                newBlock: '[data-amwishlist-js="new-block"]',
                newForm: '[data-amwishlist-js="newlist-form"]',
                formKeyInput: 'input[name="form_key"]',
                wishlistAddButton: '[data-mwishlist-popup]',
                wishlistAddPopup: '[data-mwishlist-form]',
                wishlistDeleteButton: '[data-mwishlist-delete]',
                productNode: '.product-item, .product.info, .item, .main',
                productItem: '[data-amwishlist-js="product-item"]',
                productForm: 'form[data-role="tocart-form"], #product_addtocart_form',
                wishlistItemInCart: '[data-role="tocart"]',
                selectAllButton: '[data-amwishlist-js="select-all"]',
                itemCheckbox: '[data-amwishlist-js="item-checkbox"]',
                copyButton: '[data-amwishlist-js="copy"]',
                moveButton: '[data-amwishlist-js="move"]',
                wishlistForm: '[data-amwishlist-js="wishlist-form"]',
                itemQty: '[data-amwishlist-js="item-qty"]',
                itemCopy: '[data-amwishlist-js="item-copy"]',
                itemMove: '[data-amwishlist-js="item-move"]',
                itemRemove: '[data-amwishlist-js="item-remove"]',
                itemNode: '[data-amwishlist-js="product-item"]',
                tabsWidget: '[data-amwishlist-js="tabs"]'
            },
            deleteMsg: $t('Are you sure? This action can\'t be undone.'),
            cookieName: 'amasty_wishlist_data'
        },
        classes: {
            active: '-active',
            disabled: '-disabled'
        },
        nodes: {},

        _create: function () {
            var self = this;

            self.nodes.newForm = $(self.options.selectors.newForm);
            self.nodes.newBlock = $(self.options.selectors.newBlock);
            self.nodes.newBlockInputs = $(self.options.selectors.newBlock + ' input');

            self.element.on('submit', self.options.selectors.wishlistAddPopup, function (event) {
                event.preventDefault();

                self.ajaxAction(event.currentTarget, $(self.options.selectors.wishlistAddPopup).serializeArray());
            });

            if (!this.options.loginUrl && self._getWishlistData()) {
                self._preOpenPopup();
            } else {
                $(document).on('customer-data-reload', function (event, sectionNames) {
                    if (sectionNames.indexOf('mwishlist') !== -1) {
                        self._getWishlistSection().subscribe(function () {
                            self._preOpenPopup();
                        });
                    }
                });
            }

            registry.get('ampopup', function (component) {
                self.popup = component;
            });
            registry.get('ampopup.amwishlist', function (component) {
                self.wishlist = component;
            });

            self._bindActions();
        },

        ajaxAction: function (target, additionalData, callback) {
            var self = this,
                formKey = $(this.options.selectors.formKeyInput).val(),
                formData = target instanceof HTMLElement ? $(target).data('mwishlist-ajax') : target;

            if (formKey) {
                formData.data['form_key'] = formKey;
            }

            if (additionalData) {
                $.each(additionalData, function (index, entry) {
                    formData.data[entry.name] = entry.value;
                });
            }

            $.ajax({
                url: formData.action,
                type: 'post',
                dataType: 'json',
                data: formData.data,
                success: function (response) {
                    var redirectUrl = formData.redirect || response.backUrl;

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }

                    if (response.blocks) {
                        reloadBlocks(response.blocks);
                        self._toggleMassActions();
                    }

                    if (response.components) {
                        reloadComponents(response.components);
                    }

                    if ($('body.checkout-cart-index').length) {
                        window.location.reload();
                        return true;
                    }

                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            });

            if (self._getWishlistData()) {
                self._preOpenPopup();
            } else {
                $(document).on('customer-data-reload', function (event, sectionNames) {
                    if (sectionNames.indexOf('mwishlist') !== -1) {
                        self._getWishlistSection().subscribe(function () {
                            self._preOpenPopup();
                        });
                    }
                });
            }
        },

        /**
         * Open popup on page after customer login.
         *
         * @private
         */
        _preOpenPopup: function () {
            var savedData = $.mage.cookies.get(this.options.cookieName)
                ? JSON.parse($.mage.cookies.get(this.options.cookieName))
                : {};

            if (window.location.href.indexOf(savedData.referer) !== -1) {
                this._openAddItemPopup(savedData);
                $.mage.cookies.clear(this.options.cookieName);
            }
        },

        _bindActions: function () {
            var events = {};

            events['click ' + this.options.selectors.wishlistAddButton] = '_tryOpenPopup';
            events['click ' + this.options.selectors.wishlistDeleteButton] = '_deleteWishlist';
            events['click ' + this.options.selectors.wishlistItemInCart] = '_moveItemInCart';
            events['change ' + this.options.selectors.itemCheckbox] = '_toggleCheckbox';
            events['click ' + this.options.selectors.moveButton] = '_moveAllItems';
            events['click ' + this.options.selectors.copyButton] = '_copyAllItems';
            events['submit ' + this.options.selectors.newForm] = '_createWishlist';
            events['click ' + this.options.selectors.selectAllButton] = '_checkAllItems';
            events['click ' + this.options.selectors.itemCopy] = '_copyOneItem';
            events['click ' + this.options.selectors.itemMove] = '_moveOneItem';
            events['click ' + this.options.selectors.itemRemove] = '_removeItem';

            this._on(events);
        },

        _createWishlist: function (event) {
            var self = this,
                additionalData = self.nodes.newForm.serializeArray(),
                typeId = $(self.options.selectors.tabsWidget).amPageTabs('getActiveTabId');

            event.preventDefault();

            additionalData.push({
                name: 'wishlist[type]',
                value: typeId
            });

            additionalData.push({
                name: 'current_tab',
                value: typeId
            });

            this.ajaxAction(event.currentTarget, additionalData, function () {
                self.nodes.newBlock.collapsible('deactivate');
                self.nodes.newBlockInputs.val('');
            });
        },

        _moveItemInCart: function (event) {
            event.preventDefault();
            this.ajaxAction(event.currentTarget);
        },

        _deleteWishlist: function (event) {
            var self = this,
                target = event.currentTarget;

            confirm({
                content: this.options.deleteMsg,
                actions: {
                    /**
                     * Confirm action.
                     */
                    confirm: function () {
                        self.ajaxAction(target);
                    }
                }
            });
        },

        _tryOpenPopup: function (event) {
            var addToParams = this._getAddParams(event.currentTarget);

            event.preventDefault();

            if (this.options.loginUrl) {
                addToParams.referer = window.location.href;
                $.mage.cookies.set(this.options.cookieName, JSON.stringify(addToParams), {'lifetime': 60});
                window.location.href = this.options.loginUrl;
            } else {
                this._openAddItemPopup(addToParams);
            }
        },

        _getAddParams: function (wishlistButton) {
            var params = $(wishlistButton).data('mwishlist-ajax'),
                addToCartParams = $(wishlistButton).closest(this.options.selectors.productNode)
                    .find(this.options.selectors.productForm).serializeArray();

            $.each(addToCartParams, function (index, entry) {
                params.data[entry.name] = entry.value;
            });

            return params;
        },

        _openAddItemPopup: function (ajaxSettings) {
            this._openPopup(
                $t('Choose the list for selected product'),
                $t('Add to List'),
                ajaxSettings
            )
        },

        /**
         * Amasty PopUp Component Init
         *
         * @returns Void
         * @private
         */
        _openPopup: function (popupTitle, actionTitle, ajaxSettings, wishlistIdName, excludeCurrent) {
            var self = this;

            if (typeof wishlistIdName === 'undefined') {
                wishlistIdName = 'wishlist_id';
            }

            if (excludeCurrent) {
                self.wishlist.excludeIds([+ajaxSettings.data.wishlist_id]);
            }

            ajaxSettings.data[wishlistIdName] = self.wishlist.currentListId();
            self.wishlist.currentListId.subscribe(function (currentListId) {
                ajaxSettings.data[wishlistIdName] = currentListId;
            });

            self.popup.contentTmpl('Amasty_MWishlist/components/popup/wishlist');
            self.popup.header(popupTitle);
            self.popup.show();
            self.popup.buttons({
                text: $t(actionTitle),
                classes: '-fill -primary -addtolist -disabled',
                callback: function () {
                    self.ajaxAction(ajaxSettings);
                    self.popup.hide();
                },
                disableDependency: self.wishlist.currentListId
            });
        },

        /**
         * Return false if Section not loaded yet, else return Section data.
         *
         * @returns {boolean|Object}
         * @private
         */
        _getWishlistData: function () {
            var result = false;

            if (this._validateWishlistSectionData()) {
                result = this._getWishlistSection()()['wishlist_list'];
            }

            return result;
        },

        _validateWishlistSectionData: function () {
            var result = true;

            if (!this._getWishlistSection()
                || typeof this._getWishlistSection()()['wishlist_list'] === 'undefined'
            ) {
                customerData.reload(['mwishlist']);
                result = false;
            }

            return result;
        },

        _getWishlistSection: function () {
            return customerData.get('mwishlist');
        },

        _isAnyItemsChecked: function () {
            return !!$(this.options.selectors.itemCheckbox + ':checked').length;
        },

        _checkAllItems: function () {
            if (this._isAnyItemsChecked()) {
                $(this.options.selectors.itemCheckbox).attr('checked', false);
            } else {
                $(this.options.selectors.itemCheckbox).attr('checked', true);
            }
            $(this.options.selectors.itemCheckbox).trigger('change');
        },

        _toggleCheckbox: function (event) {
            var parent = $(event.target).closest(this.options.selectors.productItem);

            parent.toggleClass(this.classes.active);
            this._toggleMassActions();
        },

        _toggleMassActions: function () {
            if (this._isAnyItemsChecked()) {
                $(this.options.selectors.selectAllButton).text($t('Unselect all'));
                $(this.options.selectors.moveButton).removeClass(this.classes.disabled);
                $(this.options.selectors.copyButton).removeClass(this.classes.disabled);
            } else {
                $(this.options.selectors.selectAllButton).text($t('Select all'));
                $(this.options.selectors.moveButton).addClass(this.classes.disabled);
                $(this.options.selectors.copyButton).addClass(this.classes.disabled);
            }
        },

        _moveAllItems: function (event) {
            this._moveItems(event, this.options.selectors.wishlistForm);
        },

        _copyAllItems: function (event) {
            this._copyItems(event, this.options.selectors.wishlistForm);
        },

        _moveItems: function (event, scope) {
            this._openPopup(
                $t('Please choose a List'),
                $t('Move'),
                this._getMoveParams(event.currentTarget, scope),
                'to_wishlist_id',
                true
            );
        },

        _copyItems: function (event, scope) {
            this._openPopup(
                $t('Please choose a List'),
                $t('Copy'),
                this._getMoveParams(event.currentTarget, scope),
                'to_wishlist_id',
                true
            );
        },

        _getMoveParams: function (button, scope) {
            var params = $(button).data('mwishlist-ajax'),
                addToCartParams = $(button).closest(scope).find('input').serializeArray();

            $.each(addToCartParams, function (index, entry) {
                params.data[entry.name] = entry.value;
            });

            return params;
        },

        _copyOneItem: function (event) {
            this._copyItems(event, this.options.selectors.itemNode);
        },

        _moveOneItem: function (event) {
            this._moveItems(event, this.options.selectors.itemNode);
        },

        _removeItem: function (event) {
            this.ajaxAction(event.currentTarget);
        }
    });

    return $.mage.amMultipleWishlist;
});
