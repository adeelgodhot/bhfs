<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Block\Adminhtml;

use Magento\Backend\Block\Template;

class Config extends Template
{
    /**
     * @return array
     */
    public function getParams()
    {
        return [
            'store'   => $this->getRequest()->getParam('store'),
            'website' => $this->getRequest()->getParam('website')
        ];
    }
}
