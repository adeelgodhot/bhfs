<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Amasty\MWishlist\Model\ResourceModel\LoadSkuByIds;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;

class DataProviderPlugin
{
    /**
     * @var LoadSkuByIds
     */
    private $loadSkuByIds;

    public function __construct(LoadSkuByIds $loadSkuByIds)
    {
        $this->loadSkuByIds = $loadSkuByIds;
    }

    /**
     * Add SKU data for each product. Magento merge SKUs for simples as for other searchable attributes in DataProvider
     * @param DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetProductAttributes(DataProvider $subject, $result)
    {
        if (is_array($result)) {
            $productSkuData = $this->loadSkuByIds->execute(array_keys($result));
            $skuAttributeId = $subject->getSearchableAttribute('sku')->getAttributeId();
            foreach ($result as $entityId => $entityData) {
                if (isset($productSkuData[$entityId])) {
                    $result[$entityId][$skuAttributeId] = $productSkuData[$entityId];
                }
            }
        }

        return $result;
    }

    /**
     * Remove SKU from parent product data.
     * Make this because we want that magento process SKU as other searchable attributes in prepareProductIndex
     *
     * @param DataProvider $subject
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     */
    public function beforePrepareProductIndex(DataProvider $subject, $indexData, $productData, $storeId)
    {
        unset($productData['sku']);

        return [$indexData, $productData, $storeId];
    }
}
