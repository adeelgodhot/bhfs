<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\ResourceModel\DebugContext;

use Amasty\Fpc\Model\Debug\DebugContext;
use Amasty\Fpc\Model\ResourceModel\DebugContext as DebugContextResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->_init(DebugContext::class, DebugContextResource::class);
    }
}
