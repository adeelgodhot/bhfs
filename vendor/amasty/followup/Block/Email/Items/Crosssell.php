<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Email\Items;

class Crosssell extends \Magento\Checkout\Block\Cart\Crosssell
{
    public function getQuote()
    {
        return $this->getData('quote');
    }
}