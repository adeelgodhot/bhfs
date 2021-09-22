<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


declare(strict_types=1);

namespace Amasty\Amp\Block\Checkout\Cart;

class Sidebar extends \Magento\Checkout\Block\Cart\Sidebar
{
    /**
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function startSession()
    {
        $this->_checkoutSession->start();
    }

    /**
     * @return Sidebar
     * @throws \Magento\Framework\Exception\SessionException
     */
    public function _beforeToHtml()
    {
        $this->startSession();
        return parent::_beforeToHtml();
    }
}
