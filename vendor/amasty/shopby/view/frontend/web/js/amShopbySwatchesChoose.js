define([
    "jquery"
], function ($) {
    'use strict';

    $.widget('mage.amShopbySwatchesChoose', {
        options: {
            listSwatches: {},
            swatchWidgetName: 'mageSwatchRenderer'
        },

        _create: function () {
            var self = this;
            if (self.options.listSwatches.length) {
                setTimeout(function () {
                        self.element.find('[data-role^="swatch-option"]').each( function (i, element) {
                            var $element = $(element),
                                swatchWidget = $element.data(self.options.swatchWidgetName);
                            if (!swatchWidget || !swatchWidget._EmulateSelected) {
                                return;
                            }

                            $(self.options.listSwatches).each( function (id, attribute) {
                                swatchWidget._EmulateSelected(attribute);
                            });
                        });
                }, 100);
            }
        }
    });

    return $.mage.amShopbySwatchesChoose;
});
