define([
    "Magento_Ui/js/grid/columns/actions",
], function (Actions) {
    return Actions.extend({
        defaults: {
            bodyTmpl: 'Amasty_Fpc/grid/columns/show-backtrace',
            modules: {
                cacheBacktraceModal: 'index = flush-cache-backtrace-modal',
                cacheBacktrace: 'index = flush-cache-backtrace'
            }
        },
        showBacktraceModal: function (backtrace) {
            this.cacheBacktraceModal().openModal();
            this.cacheBacktrace().setBacktrace(backtrace);
            return this;
        },
    });
});
