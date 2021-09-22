<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\ResourceModel\Template;

class Collection extends \Magento\Email\Model\ResourceModel\Template\Collection
{
    public function toOptionArray(): array
    {
        $this->filterByTemplateCode(['eq' => 'mwishlist_price_alert_notifications_template']);

        return $this->_toOptionArray('template_id', 'template_code');
    }

    public function filterByTemplateCode(array $condition): void
    {
        $this->addFieldToFilter('orig_template_code', $condition);
    }
}
