/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
define([
    'jquery'
], function($) {
    "use strict";

    $.widget('mageside.loadRecipeList', {

        options: {
            url: null,
            method: 'GET',
            recipeMoreBlock: '.recipe-list-wrapper .recipe-grid'
        },

        _create: function() {
            this._bind();
        },

        _bind: function() {
            var self = this;
            var countPage = function () {
                var collectionItems = self.options.items;
                var itemsPerPage = self.options.itemsPerPage;
                if (collectionItems  && itemsPerPage) {
                    return collectionItems / itemsPerPage;
                }
            };

            var page = 1;

            if (countPage() > page) {
                $('.more-recipes').addClass('active');
            } else {
                $('.more-recipes').removeClass('active');
            }
            $('#update-recipes').on('click', function (event) {
                event.preventDefault();
                page += 1;
                var params = '';
                var productId = self.options.productId;
                if (productId) {
                    params += 'productId/' + productId + '/';
                }
                if (countPage() <= page) {
                    $('.recipe-list-wrapper .more-recipes').hide();
                }
                params += 'page/' + page;
                self._ajaxSubmit(params);
            });
        },

        _ajaxSubmit: function(selectedFilters) {
            var self = this;

            $.ajax({
                showLoader: true,
                url: self.options.url + selectedFilters,
                type: self.options.method,
                dataType: 'json',
                success: function(response) {
                    if (response.recipes) {
                        $(self.options.recipeMoreBlock).append(response.recipes);
                    }
                }
            });
        }
    });
    return $.mageside.loadRecipeList;
});
