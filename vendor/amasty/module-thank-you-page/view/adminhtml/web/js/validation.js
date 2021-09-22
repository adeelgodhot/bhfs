require([
        'jquery',
        'mage/translate',
        'jquery/validate'
    ],
    function ($) {
        $.validator.addMethod(
        'validate-greater-than-zero-for-static-block',
            function (v) {
                if ($.mage.isEmptyNoTrim(v)) {
                    return true;
                }
                v = $.mage.parseNumber(v);

                return !isNaN(v) && v > 0;
            },
            $.mage.__('Please, select a static block.')
        );
    }
);
