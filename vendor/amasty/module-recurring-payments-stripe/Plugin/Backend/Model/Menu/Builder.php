<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


namespace Amasty\RecurringStripe\Plugin\Backend\Model\Menu;

use Magento\Backend\Model\Menu;

/**
 * Class Builder
 * Hides modules's menu from Amasty menu
 */
class Builder
{
    const MENU_ID = 'Amasty_RecurringStripe::container';

    public function afterGetResult($subject, Menu $menu)
    {
        $menu->remove(self::MENU_ID);

        return $menu;
    }
}
