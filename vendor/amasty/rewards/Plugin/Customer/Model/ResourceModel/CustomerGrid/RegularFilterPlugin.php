<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rewards
 */


declare(strict_types=1);

namespace Amasty\Rewards\Plugin\Customer\Model\ResourceModel\CustomerGrid;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter as OriginalFilter;

class RegularFilterPlugin
{
    const TABLE_NAME = 'customer_grid_flat';

    /**
     * @param OriginalFilter $subject
     * @param Collection $collection
     * @param Filter $filter
     *
     * @return array
     * @see \Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter::apply();
     *
     */
    public function aroundApply(OriginalFilter $subject, callable $proceed, Collection $collection, Filter $filter)
    {
        if ($collection->getMainTable() == $collection->getTable(self::TABLE_NAME)) {
            if ($filter->getField() == 'amount') {
                $filter->setField('amrewards.points_left');
            }
            if (((int)$filter->getValue() <= 0 && $filter->getConditionType() == 'gteq')
                || $filter->getConditionType() == 'lteq'
            ) {
                $collection->addFieldToFilter(
                    [$filter->getField(), $filter->getField()],
                    [
                        [$filter->getConditionType() => $filter->getValue()],
                        ['is' => new \Zend_Db_Expr('null')]
                    ]
                );
            } else {
                return $proceed($collection, $filter);
            }
        } else {
            return $proceed($collection, $filter);
        }
    }
}
