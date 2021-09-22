<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Modifyallprices extends Modifyprice
{
    const TYPE = 'modifyallprices';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($eavConfig, $storeManager, $resource, $entityResolver, $configProvider);
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Update All Types of Prices')->render(),
            'confirm_message' => __('Are you sure you want to update all types of prices?')->render(),
            'type' => $this->type,
            'label' => __('Update All Types of Prices')->render(),
            'fieldLabel' => __('By')->render(),
            'placeholder' => __('+12.5, -12.5, +12.5%')->render()
        ];
    }

    protected function updateAttribute(string $attrCode, array $productIds, int $storeId, string $value): void
    {
        $priceCodes = [];
        $attributes = $this->collectionFactory->create()
            ->addVisibleFilter()
            ->addFieldToFilter(AttributeInterface::FRONTEND_INPUT, 'price');

        foreach ($attributes as $attribute) {
            $priceCodes[$attribute->getId()] = $attribute->getAttributeCode();
        }
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'price');
        $table = $attribute->getBackend()->getTable();
        $entityIdName = $this->connection->quoteIdentifier(
            $this->entityResolver->getEntityLinkField(ProductInterface::class)
        );

        $where = [
            $this->connection->quoteInto($entityIdName . ' IN(?)', $productIds),
            $this->connection->quoteInto('attribute_id IN(?)', array_keys($priceCodes))
        ];
        $defaultStoreId = Store::DEFAULT_STORE_ID;
        $storeIds = [];

        if ($storeId && $defaultStoreId != $storeId) {
            $storeIds = $this->storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
        } else { // all stores
            $stores = $this->storeManager->getStores(true);

            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        }
        $where[] = $this->connection->quoteInto('store_id IN(?)', $storeIds);
        $where[] = new \Zend_Db_Expr('value IS NOT NULL');

        // update all price attributes
        $sql = $this->prepareQuery($table, $value, $where);
        $this->connection->query($sql);

        //update tier price
        $websiteId = $this->storeManager->getStore($storeId)->getWebsite()->getId();
        $where = [
            $this->connection->quoteInto($entityIdName . ' IN(?)', $productIds),
            $this->connection->quoteInto('website_id = ?', $websiteId)
        ];
        $table = $this->resource->getTableName('catalog_product_entity_tier_price');
        $sql = $this->prepareQuery($table, $value, $where);
        $this->connection->query($sql);
    }
}
