define([
    'Amasty_RecurringPayments/js/form/element/abstract',
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        checkVisibility: function (value) {
            if (this.fieldSet) {
                _.each(this.fieldSet.elems(), function (container) {
                    _.each(container.elems(), function (field) {
                        if (require('uiRegistry').get(field)) {
                            if (_.contains(this.imports.allowedFields, field.index)) {
                                if (this.imports.allowedFields[value] === field.index) {
                                    field.visible(true);
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
