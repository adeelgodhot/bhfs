define([
    'jquery',
    'magnificPopup'
], function ($, magnificPopup) {
    "use strict";
    return {
        displayContent: function (prodUrl, ratioImage) {
            if (!prodUrl.length) {
                return false;
            }
            var url = QUICKVIEW_BASE_URL + 'mgs_quickview/index/updatecart';
            $.magnificPopup.open({
                items: {
                    src: prodUrl
                },
                type: 'iframe',
                removalDelay: 300,
                mainClass: 'mfp-fade mfp-mgs-quickview ratio-' + ratioImage,
                closeOnBgClick: false,
                preloader: true,
                tLoading: '',
                callbacks: {
                    beforeClose: function () {
                        $('[data-block="minicart"]').trigger('contentLoading');
                        $.ajax({
                            url: url,
                            method: "POST"
                        });
                    }
                }
            });
        }
    };
});
