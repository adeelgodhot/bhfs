define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    return function (config, element) {
        config.buttons = [
            {
                text: $.mage.__('I have read and accept'),
                'class': 'action action-primary',
                click: function () {
                    var checkbox = $($('#amgdpr-privacy-popup').data('amgdpr-checkbox-selector'));
                    checkbox.prop('checked', true);
                    checkbox.trigger('change');
                    this.closeModal();
                }
            }
        ];
        var popup = modal(config, element),
            textUrl = config.textUrl;
        $(document).on('click', '[data-role="amasty-gdpr-consent"] a[href="#"]',function (e) {
            var targetCheckbox = $(this).closest('div[data-role="amasty-gdpr-consent"]').find('input[type="checkbox"]');

            e.preventDefault();
            e.stopPropagation();
            $.ajax({
                async: false,
                url: textUrl,
                data: { consent_id: targetCheckbox.data('consent-id') },
                success: function (response) {
                    popup.setTitle(response.title);
                    popup.element.html(response.content);
                }
            });
            popup.openModal().on('modalclosed', function () {
                popup.element.html('');
            });
            $('#amgdpr-privacy-popup').closest('.modal-popup').css('z-index', 100001);
            $('#amgdpr-privacy-popup').data('amgdpr-checkbox-selector', '#' + targetCheckbox.attr('id'));
            $('.modals-overlay').css('z-index', 100000);
            return false;
        });
    };
});
