define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component, rendererList) {
        'use strict';

        rendererList.push(
            {
                type: 'amasty_recurring_paypal',
                component: 'Amasty_RecurringPaypal/js/view/payment/method-renderer/paypal'
            }
        );

        return Component.extend({});
    }
);