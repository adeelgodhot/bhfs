<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */


declare(strict_types=1);

namespace Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice;

use Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice;
use Magento\Framework\View\Element\Template;

class Checkbox extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Paction::tier_prices_checkbox.phtml';

    public function getCheckboxElementName(): string
    {
        return TierPrice::TIER_PRICE_CHANGE_CHECKBOX_NAME;
    }

    public function getCheckboxElementId(): string
    {
        return 'toggle_' . $this->getData(TierPrice::TIER_PRICE_CHECKBOX_ID);
    }
}
