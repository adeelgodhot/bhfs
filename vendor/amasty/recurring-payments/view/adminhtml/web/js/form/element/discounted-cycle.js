define([
    'Amasty_RecurringPayments/js/form/element/abstract',
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        onChangeAvailableDiscount: function () {
            this.checkVisibility(this.value());
        },

        checkVisibility: function (value) {
            if (this.fieldSet) {
                _.each(this.fieldSet.elems(), function (container) {
                    _.each(container.elems(), function (field) {
                        if (require('uiRegistry').get(field)) {
                            if (_.contains(this.imports.allowedFields, field.index)) {
                                field.visible(value == this.imports.allowed
                                    && require('uiRegistry').get(this.imports.globalSetting).value() == this.imports.globalSettingValue
                                    && require('uiRegistry').get(this.imports.fieldSet).value() == '1'
                                );
                            }

                            if (_.contains(this.imports.discountFields, field.index)) {
                                var discountType = require('uiRegistry').get('product_form.product_form.subscription-settings.container_am_discount_type.am_discount_type');

                                if (this.imports.discountFields[discountType.value()] === field.index && discountType.visible()) {
                                    field.visible(require('uiRegistry').get(this.imports.fieldSet).value() == '1');
                                } else {
                                    field.visible(false);
                                }
                            }
                        }
                    }.bind(this));
                }.bind(this));
            }
        }
    });
});
