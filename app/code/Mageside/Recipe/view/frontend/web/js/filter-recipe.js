/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
define([
    'jquery'
], function($) {
    "use strict";

    $.widget('mageside.recipeFilter', {

        options: {
            url: null,
            method: 'GET',
            triggerEvent: 'change',
            recipeBlockSelector: '.recipe-list-wrapper .recipe-grid'
        },

        _create: function() {
            this._bind();
        },

        _bind: function() {
            var self = this;
            $('#form-search').submit(function (event) {
                event.preventDefault();
                self._ajaxSubmit($(event.currentTarget));
            });
            self.element.find('select').on(self.options.triggerEvent, function() {
                self._ajaxSubmit($('#form-search'));
            });
        },

        _ajaxSubmit: function($form) {
            var self = this,
                data = {};

            if ($form) {
                _.each($('input', $form), function(element) {
                    if (element.value) {
                        data[element.name] = element.value;
                    }
                });
            }
            _.each(self.element.find('select'), function (element) {
                if (element.value) {
                    data[element.name] = element.value;
                }
            });

            if ($('.writer-url-key').val()) {
                data.writer = $('.writer-url-key').val();
            }

            $.ajax({
                showLoader: true,
                url: self.options.url,
                type: self.options.method,
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.recipes) {
                        $(self.options.recipeBlockSelector).html(response.recipes);
                    }
                    $('body').trigger('contentUpdated');
                }
            });
        }

    });

    return $.mageside.recipeFilter;
});
