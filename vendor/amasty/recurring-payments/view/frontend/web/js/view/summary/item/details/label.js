define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * Checks visibility.
         *
         * @return {Boolean}
         */
        isVisible: function (itemId) {
            return this.recurring_items[itemId];
        },
    });
});
