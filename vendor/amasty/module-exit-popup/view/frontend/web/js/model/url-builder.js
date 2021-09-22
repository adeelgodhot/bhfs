define(['jquery'], function ($) {
    'use strict';

    return {
        method: 'rest/',
        version: 'V1',

        /**
         * @param {String} url
         * @param {String} storeCode
         * @return {*}
         */
        createUrl: function (url, storeCode) {
            var completeUrl = this.method + storeCode + this.version + url;

            return completeUrl;
        }
    };
});
