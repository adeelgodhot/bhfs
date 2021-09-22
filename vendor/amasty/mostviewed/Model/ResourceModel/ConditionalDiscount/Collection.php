<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount as ConditionalDiscountModel;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount as ConditionalDiscountResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ConditionalDiscountInterface::ID;

    public function _construct()
    {
        $this->_init(ConditionalDiscountModel::class, ConditionalDiscountResource::class);
    }
}
