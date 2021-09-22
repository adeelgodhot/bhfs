define([
    "jquery",
    "Magento_Ui/js/modal/modal"
], function ($) {

    $.widget('mage.amrecCreateHook', {
        options: {
            create_url: null
        },

        _create: function () {
            $('#create_button').on('click', this.createWebhook.bind(this));
        },

        createWebhook: function () {
            $('#config-edit-form').attr('action', this.options.create_url).submit();
        }
    });

    return $.mage.amrecCreateHook;
});
