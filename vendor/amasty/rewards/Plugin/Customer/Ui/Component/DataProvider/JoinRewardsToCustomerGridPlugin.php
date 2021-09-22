<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rewards
 */

declare(strict_types=1);

namespace Amasty\Rewards\Plugin\Customer\Ui\Component\DataProvider;

use Magento\Customer\Ui\Component\DataProvider;
use Magento\Framework\Api\Search\SearchResultInterface;

class JoinRewardsToCustomerGridPlugin
{
    const ALIAS_FIELD_NAME = 'amount';
    const ALIAS_TABLE_NAME = 'amrewards';
    const TABLE_FIELD = 'points_left';
    const TABLE_NAME = 'customer_grid_flat';
    const REWARDS_TABLE = 'amasty_rewards_rewards';

    /**
     * @param DataProvider $subject
     * @param SearchResultInterface $collection
     *
     * @return SearchResultInterface
     */
    public function afterGetSearchResult(
        DataProvider $subject,
        SearchResultInterface $collection
    ): SearchResultInterface {
        if ($collection->getMainTable() === $collection->getTable(self::TABLE_NAME)) {
            $aliasForTableAgain = self::ALIAS_TABLE_NAME . '_again';
            $collection
                ->getSelect()
                ->joinLeft(
                    [self::ALIAS_TABLE_NAME => $collection->getTable(self::REWARDS_TABLE)],
                    'main_table.entity_id = ' . self::ALIAS_TABLE_NAME . '.customer_id',
                    [self::ALIAS_FIELD_NAME => self::TABLE_FIELD]
                )->joinLeft(
                    [$aliasForTableAgain => $collection->getTable(self::REWARDS_TABLE)],
                    $aliasForTableAgain . '.customer_id = ' . self::ALIAS_TABLE_NAME . '.customer_id AND '
                    . self::ALIAS_TABLE_NAME . '.id < ' . $aliasForTableAgain . '.id',
                    []
                )->where($aliasForTableAgain . '.id IS NULL');
        }

        return $collection;
    }
}
