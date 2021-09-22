/**
 *  Amasty swatch render mixin. Hiding simple product price that is part of configurable product
 */

define([
    'jquery',
    'underscore',
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            options: {
                selectors: {
                    buttons: '.box-tocart, .tocart',
                    optionSelected: 'data-option-selected',
                    attributeId: 'data-attribute-id'
                }
            },

            _UpdatePrice: function () {
                var $widget = this,
                    $selectors = $widget.options.selectors,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find($widget.options.selectorProductPrice),
                    $productButton = $product.find($selectors.buttons),
                    options = _.object(_.keys($widget.optionsMap), {}),
                    selectedValues = [],
                    result,
                    isNotAllSelected;

                $widget.element
                    .find('.' + $widget.options.classes.attributeClass + '[' + $selectors.optionSelected + ']')
                    .each(function () {
                        options[$(this).attr($selectors.attributeId)] = $(this).attr($selectors.optionSelected);
                });

                for (const property in options) {
                    selectedValues.push(options[property]);
                }

                isNotAllSelected = selectedValues.some(function (element) {
                   return typeof element === 'undefined';
                });

                result = $widget._getNewPrices();

                if (!isNotAllSelected) {
                    this._super();
                }

                if (result && result.finalPrice && result.finalPrice.amount === 0 && !isNotAllSelected) {
                    $productPrice.hide();
                    $productButton.hide();
                } else {
                    $productPrice.show();
                    $productButton.show();
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});
