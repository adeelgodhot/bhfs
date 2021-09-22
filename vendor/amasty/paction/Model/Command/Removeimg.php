<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\EntityResolver;
use Amasty\Paction\Model\GetProductCollectionByIds;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;

class Removeimg extends Command
{
    const MEDIA_GALLERY_ATTRIBUTE_CODE = 'media_gallery';
    const PRODUCT_ENTITY_VARCHAR_TABLE = 'catalog_product_entity_varchar';
    const TYPE = 'removeimg';

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectoryWrite;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var EntityResolver
     */
    protected $entityResolver;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var GetProductCollectionByIds
     */
    protected $getProductCollectionByIds;

    /**
     * @var array
     */
    protected $imgAttributeIdsBySetId = [];

    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        Config $eavConfig,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        Filesystem $filesystem,
        MediaConfig $mediaConfig,
        EntityResolver $entityResolver,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->mediaDirectoryWrite = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
        $this->entityResolver = $entityResolver;
        $this->getProductCollectionByIds = $getProductCollectionByIds;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Images')->render(),
            'confirm_message' => __('Are you sure you want to remove images?')->render(),
            'type' => $this->type,
            'label' => __('Remove Images')->render(),
            'fieldLabel' => ''
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (empty($ids)) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);
        $entityTypeId = (int)$this->eavConfig->getEntityType(Product::ENTITY)->getId();
        $mediaGalleryAttribute = $this->eavConfig->getAttribute($entityTypeId, self::MEDIA_GALLERY_ATTRIBUTE_CODE);
        $mediaGalleryAttributeId = (int)$mediaGalleryAttribute->getId();

        // we do not use store ID as it is a global action
        foreach ($this->getProductCollectionByIds->get($ids, $entityIdName) as $product) {
            $imageAttributeIds = $this->getAttributeIdsBySetId((int)$product->getAttributeSetId(), $entityTypeId);
            $this->removeDataAndFiles(
                $mediaGalleryAttributeId,
                (int)$product->getData($entityIdName),
                $imageAttributeIds,
                $entityIdName
            );
        }

        return __('Images and labels has been successfully deleted');
    }

    private function removeDataAndFiles(
        int $mediaGalleryAttributeId,
        int $productId,
        array $imageAttributeIds,
        string $entityIdName
    ): void {
        $mediaGalleryTable = $this->resource->getTableName(Gallery::GALLERY_TABLE);
        $quotedEntityIdName = $this->resource->getConnection()->quoteIdentifier($entityIdName);

        // Delete varchar
        foreach ($imageAttributeIds as $attributeId) {
            $this->connection->delete(
                $this->resource->getTableName(self::PRODUCT_ENTITY_VARCHAR_TABLE),
                [
                    'attribute_id = ?' => $attributeId,
                    $quotedEntityIdName . ' = ?' => $productId
                ]
            );
        }

        $select = $this->connection->select()
            ->from($mediaGalleryTable, ['value_id', 'value'])
            ->joinInner(
                ['entity' => $this->resource->getTableName(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE)],
                $mediaGalleryTable . '.value_id = entity.value_id',
                [$entityIdName => $entityIdName]
            )
            ->where('attribute_id = ?', $mediaGalleryAttributeId)
            ->where($quotedEntityIdName . ' = ?', $productId);

        $valueIds = [];
        // Delete files
        foreach ($this->connection->fetchAll($select) as $row) {
            $imgFilePath = $this->mediaDirectoryWrite
                ->getAbsolutePath($this->mediaConfig->getMediaShortUrl($row['value']));

            if ($this->mediaDirectoryWrite->isFile($imgFilePath)) {
                try {
                    $this->mediaDirectoryWrite->delete($imgFilePath);
                } catch (FileSystemException $e) {
                    $this->errors[] = __('Can not delete image file: %1', $imgFilePath);
                }
            } else {
                $this->errors[] = __('%1 is not a file', $imgFilePath);
            }

            $valueIds[] = $row['value_id'];
        }
        // Delete media
        $this->connection->delete(
            $mediaGalleryTable,
            $this->connection->quoteInto('value_id IN(?)', $valueIds)
        );
        // Delete labels
        $this->connection->delete(
            $this->resource->getTableName(Gallery::GALLERY_VALUE_TABLE),
            $this->connection->quoteInto('value_id IN(?)', $valueIds)
        );
    }

    protected function getAttributeIdsBySetId(int $attributeSetId, int $entityTypeId): array
    {
        if (array_key_exists($attributeSetId, $this->imgAttributeIdsBySetId)) {
            return $this->imgAttributeIdsBySetId[$attributeSetId];
        }

        $imgAttributeIds = [];
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->attributeCollectionFactory->create();
        $collection->setEntityTypeFilter($entityTypeId)
            ->setAttributeSetFilter($attributeSetId)
            ->setFrontendInputTypeFilter('media_image');

        foreach ($collection as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imgAttributeIds[] = $attribute->getId();
        }
        $this->imgAttributeIdsBySetId[$attributeSetId] = $imgAttributeIds;

        return $imgAttributeIds;
    }
}
