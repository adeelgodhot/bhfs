<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class LoadSkuByIds
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function execute(array $productIds)
    {
        $select = $this->resource->getConnection()->select()->from(
            $this->resource->getTableName('catalog_product_entity'),
            ['entity_id', 'sku']
        )->where('entity_id in (?)', $productIds);

        return $this->resource->getConnection()->fetchPairs($select);
    }
}
