define([
    'underscore',
    'Magento_Ui/js/grid/massactions',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/lib/collapsible',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function (_, Massactions, registry, utils, Collapsible, confirm, alert, $t) {
    'use strict';

    return Massactions.extend({
        defaults: {
            fieldEmptyMsg: $t('Required field is empty.')
        },

        /**
         * Default action callback. Sends selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                params = {};

            params[itemsType] = data[itemsType];

            if (!params[itemsType].length) {
                params[itemsType] = false;
            }

            _.extend(params, data.params || {});

            if (action.type && action.type.indexOf('amasty') === 0) {
                params.action = action.type;
            }

            utils.submit({
                url: action.url,
                data: params
            });
        },

        applyMassaction: function (parent, action) {
            var data = this.getSelections(),
                value,
                callback;

            value = action.value;
            action = this.getAction(action.type);

            if (!value) {
                alert({ content: this.fieldEmptyMsg });

                return this;
            }

            if (!data.total) {
                alert({ content: this.noItemsMsg });

                return this;
            }

            callback = this.massactionCallback.bind(this, action, data, value);
            action.confirm ? this._confirm(action, callback) : callback();
        },

        massactionCallback: function (action, data, value) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                params = {};

            params[itemsType] = data[itemsType];

            params['amasty_paction_field'] = value;

            params.action = action.type;

            if (!params[itemsType].length) {
                params[itemsType] = false;
            }

            _.extend(params, data.params || {});

            utils.submit({
                url: action.url,
                data: params
            });
        }
    });
});
