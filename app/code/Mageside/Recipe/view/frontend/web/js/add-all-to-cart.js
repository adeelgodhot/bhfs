/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
define([
    'jquery'
], function($) {
    "use strict";

    $.widget('mageside.addAllToCart', {

        options: {
            url: null
        },

        _create: function() {
            this._bind();
        },

        _bind: function() {
            var self = this;
            self.element.on('click', function () {
                self._ajaxSubmit();
            })
        },
        _ajaxSubmit: function() {

            var self = this;

            $.ajax({
                url: self.options.url,
                type: 'post',
                dataType: 'json',
                beforeSend: function() {
                    $('body').trigger('processStart');
                },
                success: function(data) {
                    if (data === 'Ok') {
                        $('body').trigger('processStop');
                    }
                }
            });
        }

    });

    return $.mageside.addAllToCart;
});
