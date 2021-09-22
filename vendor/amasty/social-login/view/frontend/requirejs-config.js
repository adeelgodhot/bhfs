/**
 *
 * @author    Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package   Amasty_SocialLogin
 */

var config = {
    config: {
        mixins: {
            'mage/validation': {
                'Amasty_Xnotif/js/validation-mixin': false,
                'Amasty_SocialLogin/js/validation-mixin': true
            },
            'Magento_Customer/js/model/authentication-popup': {
                'Amasty_SocialLogin/js/authentication-popup-mixin' : true
            }
        }
    }
};
