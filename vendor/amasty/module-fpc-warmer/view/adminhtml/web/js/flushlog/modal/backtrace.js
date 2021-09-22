define([
    'uiElement',
], function (Element) {
    return Element.extend({
        defaults: {
            backtrace: [],
        },
        initObservable: function () {
            this._super().observe(['backtrace']);

            return this;
        },
        setBacktrace: function (backtrace) {
            this.backtrace(backtrace);
            return this;
        },
    });
});
