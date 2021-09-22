<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Birth
 */
namespace Amasty\Birth\Model\ResourceModel\Log;

class Collection
    extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init('Amasty\Birth\Model\Log',
            'Amasty\Birth\Model\ResourceModel\Log'
        );
    }
}
