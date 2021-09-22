define([
    'jquery',
    'Amasty_GdprCookie/vendor/pickr/colorpicker.min'
], function ($, Colorpicker) {
    'use strict';

    var _initEventListeners = function ($input, pickr) {
        $input.on('keydown', function () {
            return false;
        });

        $input.on('change', function (event) {
            pickr.setColor(event.target.value || null);
        });

        $input.on('click', function () {
            pickr.show();
        });

        pickr.on('change', function (color) {
            var colorHex = color ? color.toHEXA().toString() : '',
                colorRgba = color ? color.toRGBA().toString() : '';

            $input.css('backgroundColor', colorRgba).val(colorHex);
        });

        pickr.on('clear', function () {
            $input.val('').css('backgroundColor', '');
        });
    };

    return function (config) {
        var $input = $('#' + config.htmlId),
            pickr = Colorpicker.create({
                el: '#' + config.colorPickerId,
                theme: 'classic',
                useAsButton: false,
                default: config.elData ? config.elData : null,
                comparison: false,
                swatches: [
                    'rgba(244, 67, 54, 1)',
                    'rgba(233, 30, 99, 0.95)',
                    'rgba(156, 39, 176, 0.9)',
                    'rgba(103, 58, 183, 0.85)',
                    'rgba(63, 81, 181, 0.8)',
                    'rgba(33, 150, 243, 0.75)',
                    'rgba(3, 169, 244, 0.7)',
                    'rgba(0, 188, 212, 0.7)',
                    'rgba(0, 150, 136, 0.75)',
                    'rgba(76, 175, 80, 0.8)',
                    'rgba(139, 195, 74, 0.85)',
                    'rgba(205, 220, 57, 0.9)',
                    'rgba(255, 235, 59, 0.95)',
                    'rgba(255, 193, 7, 1)'
                ],
                components: {
                    preview: true,
                    opacity: true,
                    hue: true,
                    interaction: {
                        hex: true,
                        rgba: true,
                        hsla: true,
                        hsva: true,
                        cmyk: true,
                        input: true,
                        clear: true,
                        save: false
                    }
                }
            });

        $input.css('backgroundColor', config.elData);
        _initEventListeners($input, pickr);
    };
});
