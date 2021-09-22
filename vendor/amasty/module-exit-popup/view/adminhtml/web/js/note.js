define([
    "jquery",
    "collapsable",
    "mage/mage",
    "mage/backend/suggest"
],function ($) {
    'use strict';

    return function (config) {
        var suggest = $('#' + config.htmlId + '-suggest'),
            selectedContainer = $('#' + config.htmlId + '-selected');

        selectedContainer.insertAfter(selectedContainer.parent('.value').find('.note'));

        suggest
            .mage('suggest', config.selectorOptions)
            .on('suggestselect', function (e, ui) {
                if (ui.item.id) {
                    selectedContainer.show();
                    if (ui.item.sku) {
                        selectedContainer.find('span').text(ui.item.label);
                    } else {
                        selectedContainer.find('span').text('#' + ui.item.id + ' - ' + ui.item.label);
                    }
                    suggest.val('');
                } else {
                    selectedContainer.hide();
                }
            })
            .on('blur', function() {
                suggest.val('')
            });

        selectedContainer.find('a').on('click', function() {
            selectedContainer.hide();
            $('#' + config.htmlId).val('');
        });
    };
});
