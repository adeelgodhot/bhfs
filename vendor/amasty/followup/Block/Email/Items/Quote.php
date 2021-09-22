<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Email\Items;

class Quote extends \Magento\Framework\View\Element\Template
{
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }
}