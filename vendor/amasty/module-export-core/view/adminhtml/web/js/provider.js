define([
    'Magento_Ui/js/form/provider'
], function (Provider) {
    'use strict';

    return Provider.extend({
        save: function (options) {
            var data = this.get('data');

            this.client.save({encodedData: JSON.stringify(data)}, options);

            return this;
        },
    });
});
