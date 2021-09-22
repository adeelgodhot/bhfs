<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class UnsubscribePriceAlerts
{
    const MAIN_TABLE = 'ammwishlist_unsubscribed_price_alerts';

    const ID = 'id';

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    public function getUserIds(): array
    {
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName(self::MAIN_TABLE)
        );

        return $this->resource->getConnection()->fetchCol($select);
    }

    public function unsubscribeUser(int $userId): void
    {
        $this->resource->getConnection()->insert(
            $this->resource->getTableName(self::MAIN_TABLE),
            [self::ID => $userId]
        );
    }
}
