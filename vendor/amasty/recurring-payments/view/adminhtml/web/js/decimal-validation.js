require([
    'jquery',
    'Magento_Ui/js/lib/validation/utils',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function ($, utils, validator) {
    'use strict';

    validator.addRule(
        'validate-decimal',
        function (value) {
            var numValue;

            if (utils.isEmptyNoTrim(value) || !/^\s*-?\d*(\.\d*)?\s*$/.test(value)) {
                return false;
            }

            numValue = utils.parseNumber(value);

            return !isNaN(numValue);
        },
        $.mage.__('Please enter a valid floating point number in this field.')
    );
});
