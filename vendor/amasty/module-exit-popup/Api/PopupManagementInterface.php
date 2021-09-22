<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Api;

interface PopupManagementInterface
{
    /**#@+
     * Defined $pageValue values
     */
    const CART = 'cart';
    const CHECKOUT = 'checkout';
    /**#@-*/

    /**
     * @return array
     */
    public function getPopupData();

    /**
     * @param int $pageValue
     *
     * @return bool
     */
    public function isVisible($pageValue);
}
