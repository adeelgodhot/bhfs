define([
    'uiComponent',
    'uiLayout',
    'mageUtils'
], function (Component, layout, utils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_Paction/table/row',
            showWebsite: false,
            websites: [],
            groups: [],
            priceTypes: [],
            htmlClass: '',
            htmlName: '',
            priceValueValidationClass: '',
            elemIndex: 0,
            rowsConfig: {
                component: 'Magento_Ui/js/form/element/abstract',
                template: 'Amasty_Paction/table/row'
            }
        },

        deleteRow: function (element) {
            this.removeChild(element);
        },

        addRow: function () {
            var row = this.createRow(this.elemIndex);

            layout([ row ]);
            this.insertChild(row.name);
            this.elemIndex += 1;
        },

        createRow: function (index) {
            return utils.extend(this.rowsConfig, {
                'websites': this.websites,
                'groups': this.groups,
                'htmlClass': this.htmlClass,
                'htmlName': this.htmlName,
                'name': 'price-row-' + index,
                'priceValueValidationClass': this.priceValueValidationClass,
                'priceTypes': this.priceTypes
            });
        },

        getWebsite: function (name, currency) {
            currency = currency ? '[' + currency + ']' : '';

            return name + ' ' + currency;
        }
    });
});
