<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source\PageType;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product as CatalogProductUrlRewrite;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Url as FrontendUrlBuilder;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite as UrlRewrite;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;

class Product extends Rewrite
{
    /**
     * @var string
     */
    protected $rewriteType = UrlRewrite::ENTITY_TYPE_PRODUCT;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AttributeResource
     */
    private $attributeResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ProductStatus
     */
    protected $productStatus;

    public function __construct(
        UrlRewriteCollectionFactory $rewriteCollectionFactory,
        FrontendUrlBuilder $urlBuilder,
        Emulation $appEmulation,
        State $appState,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AttributeResource $attributeResource,
        MetadataPool $metadataPool,
        ProductStatus $productStatus,
        $isMultiStoreMode = false,
        array $stores = [],
        \Closure $filterCollection = null
    ) {
        parent::__construct(
            $rewriteCollectionFactory,
            $urlBuilder,
            $appEmulation,
            $appState,
            $storeManager,
            $isMultiStoreMode,
            $stores,
            $filterCollection
        );
        $this->scopeConfig = $scopeConfig;
        $this->attributeResource = $attributeResource;
        $this->metadataPool = $metadataPool;
        $this->productStatus = $productStatus;
    }

    /**
     * @param $storeId
     * @return UrlRewriteCollection
     */
    protected function getEntityCollection($storeId)
    {
        $collection = parent::getEntityCollection($storeId);

        if (!$this->isUseCategoriesPathForProductUrls($storeId)) {
            $collection->getSelect()->joinLeft(
                ['relation' => $collection->getTable(CatalogProductUrlRewrite::TABLE_NAME)],
                'main_table.url_rewrite_id = relation.url_rewrite_id',
                ['relation.category_id', 'relation.product_id']
            );
            $collection->getSelect()->where('relation.category_id IS NULL');
        }
        $this->joinAttributeStatus($collection);

        return $collection;
    }

    private function joinAttributeStatus(UrlRewriteCollection $collection)
    {
        $statusAttributeId = $this->attributeResource->getIdByCode(ModelProduct::ENTITY, 'status');
        /** @var \Magento\Framework\EntityManager\EntityMetadataInterface $metadata */
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $tableCatalogProductEntity = $collection->getTable('catalog_product_entity');
        $tableCatalogProductEntityInt = $collection->getTable('catalog_product_entity_int');
        $visibleStatusIds = $this->productStatus->getVisibleStatusIds();
        $collection->getSelect()->joinInner(
            ['product_entity' => $tableCatalogProductEntity],
            'main_table.entity_id = product_entity.entity_id',
            []
        )->joinInner(
            ['product_status_default' => $tableCatalogProductEntityInt],
            'product_status_default.' . $linkField . ' = product_entity.' . $linkField
            . ' AND product_status_default.attribute_id = ' . (int)$statusAttributeId
            . ' AND product_status_default.store_id = 0',
            []
        )->joinLeft(
            ['product_status' => $tableCatalogProductEntityInt],
            'product_status.' . $linkField . ' = product_entity.' . $linkField
            . ' AND product_status.attribute_id = ' . (int)$statusAttributeId
            . ' AND product_status.store_id = main_table.store_id',
            []
        )->where(
            $collection->getConnection()->quoteInto(
                '(product_status.value_id > 0 AND product_status.value IN (?))'
                . ' OR (product_status.value_id IS NULL AND product_status_default.value IN (?))',
                $visibleStatusIds
            )
        );
    }

    /**
     * @param $storeId
     * @return bool
     */
    private function isUseCategoriesPathForProductUrls($storeId)
    {
        if ((int)$storeId) {
            return $this->scopeConfig->isSetFlag(
                \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }

        return $this->scopeConfig->isSetFlag(\Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY);
    }
}
