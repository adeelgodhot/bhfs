define([
    'jquery',
    'uiElement',
    'underscore',
], function ($, Element, _) {
    'use strict';

    return Element.extend({
        defaults: {
            debugToggleSelector: '#debug-toggle',
            debugDataUrl: '',
            debugDataFetched: false,
            debugCurrentContextData: [],
            debugContextData: [],
        },

        initialize: function () {
            this._super();
            this.initEventListeners();

            return this;
        },

        initObservable: function () {
            this._super().observe([
                'debugCurrentContextData',
                'debugContextData',
            ]);

            return this;
        },

        initEventListeners: function () {
            $(document).on('click', this.debugToggleSelector, function (event) {
                if (!this.debugDataFetched) {
                    $.ajax({
                        url: this.debugDataUrl,
                        dataType: 'json',
                        method: 'GET',
                        data: {
                            debug_url: window.location.href
                        },
                        success: function (response) {
                            this.unpackContextData(response);
                            this.debugDataFetched = true;
                        }.bind(this)
                    });
                }

                $('.amfpc-context-container').toggleClass('-toggled');
            }.bind(this));
        },

        unpackContextData: function (response) {
            var contextData = {};

            this.debugCurrentContextData(this.extractContextData(
                response.current_context.defaults,
                response.current_context.context,
            ));

            _.each(response.page_context_data, function (pageContext, contextIndex) {
                contextData[contextIndex] =
                    this.extractContextData(pageContext.defaults, pageContext.context)
            }.bind(this));

            this.debugContextData(_.toArray(contextData));
        },

        extractContextData: function (defaults, context) {
            var result = [{
                contextKey: 'vary',
                contextValue: context.vary,
                isVary: true
            }];

            _.each(defaults, function (value, key) {
                var contextValue = context[key] ?? value;

                result.push({
                    contextKey: key,
                    contextValue: _.isString(contextValue) ? '\"' + contextValue + '\"' : contextValue,
                    isDefault: _.isUndefined(context[key]),
                });
            });

            return result;
        }
    });
});
