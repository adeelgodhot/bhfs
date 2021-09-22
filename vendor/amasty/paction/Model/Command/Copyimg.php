<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\EntityResolver;
use Amasty\Paction\Model\GetProductCollectionByIds;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Phrase;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\MediaStorage\Model\File\Uploader;

class Copyimg extends Removeimg
{
    const TYPE = 'copyimg';

    /**
     * @var Database
     */
    private $fileStorageDb;

    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        Config $eavConfig,
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        Filesystem $filesystem,
        MediaConfig $mediaConfig,
        EntityResolver $entityResolver,
        Database $database,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        parent::__construct(
            $attributeCollectionFactory,
            $eavConfig,
            $productRepository,
            $resource,
            $filesystem,
            $mediaConfig,
            $entityResolver,
            $getProductCollectionByIds
        );
        $this->fileStorageDb = $database;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Copy Images')->render(),
            'confirm_message' => __('Are you sure you want to copy images?')->render(),
            'type' => $this->type,
            'label' => __('Copy Images')->render(),
            'fieldLabel' => __('From')->render(),
            'placeholder' => __('Product ID')->render()
        ];
    }

    public function execute(array $productIds, int $storeId, string $val): Phrase
    {
        if (!$productIds) {
            throw new LocalizedException(__('Please select product(s)'));
        }
        $fromProductId = (int)trim($val);

        try {
            $fromProduct = $this->productRepository->getById($fromProductId);
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }
        $fromProductId = $this->entityResolver->getEntityLinkIds(ProductInterface::class, [$fromProductId]);

        if (isset($fromProductId[0])) {
            $fromProductId = (int)$fromProductId[0];
        }

        if (!$fromProductId) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }

        if (in_array($fromProductId, $productIds)) {
            throw new LocalizedException(__('Please remove source product from the selected products'));
        }
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        if (!$fromProduct->getData($entityIdName)) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }
        $entityTypeId = (int)$this->eavConfig->getEntityType(Product::ENTITY)->getId();
        $mediaGalleryAttribute = $this->eavConfig->getAttribute($entityTypeId, parent::MEDIA_GALLERY_ATTRIBUTE_CODE);
        $mediaGalleryAttributeId = (int)$mediaGalleryAttribute->getId();
        $parentImageAttributeIds = $this->getAttributeIdsBySetId((int)$fromProduct->getAttributeSetId(), $entityTypeId);

        // we do not use store id as it is a global action
        foreach ($this->getProductCollectionByIds->get($productIds, $entityIdName) as $product) {
            $imageAttributeIds = $this->getAttributeIdsBySetId((int)$product->getAttributeSetId(), $entityTypeId);
            $imageAttributeIds = array_values(array_intersect($parentImageAttributeIds, $imageAttributeIds));
            $isCopied = $this->copyData(
                $mediaGalleryAttributeId,
                (int)$fromProduct->getData($entityIdName),
                (int)$product->getData($entityIdName),
                $imageAttributeIds,
                $entityIdName
            );

            if (!$isCopied) {
                $this->errors[] = __('Can not copy images to product with ID %1', $product->getId());
            }
        }

        return __('Images and labels has been successfully copied');
    }

    private function copyData(
        int $mediaGalleryAttributeId,
        int $originalProductId,
        int $newProductId,
        array $imageAttributeIds,
        string $entityIdName
    ): bool {
        $mediaGalleryTable = $this->resource->getTableName(Gallery::GALLERY_TABLE);
        $mediaGalleryValueToEntityTable = $this->resource->getTableName(Gallery::GALLERY_VALUE_TO_ENTITY_TABLE);
        $productVarcharTable = $this->resource->getTableName(parent::PRODUCT_ENTITY_VARCHAR_TABLE);
        $newPic = [];

        // definition picture path
        foreach ($imageAttributeIds as $key => $attributeId) {
            $newPic[$key] = 'no_selection';
        }

        $select = $this->connection->select()
            ->from($mediaGalleryTable, ['value_id', 'value'])
            ->joinInner(
                ['entity' => $mediaGalleryValueToEntityTable],
                $mediaGalleryTable . '.value_id = entity.value_id',
                [$entityIdName => $entityIdName]
            )
            ->where('attribute_id = ?', $mediaGalleryAttributeId)
            ->where($entityIdName . ' = ?', $originalProductId);

        $selectPicFields = ['value', 'store_id'];
        $valueIdMap = [];
        // select old position of basic, small and thumb
        foreach ($imageAttributeIds as $key => $attributeId) {
            $selectPic[$key] = $this->connection->select()
                ->from($productVarcharTable, $selectPicFields)
                ->where('attribute_id = ?', $attributeId)
                ->where($entityIdName . ' = ?', $originalProductId);
            $picOrig[$key] = $this->connection->fetchRow($selectPic[$key])
                ?: array_fill_keys($selectPicFields, null);

            $storeId[$key] = $picOrig[$key]['store_id'];

            $selectPicId[$key] = $this->connection->select()
                ->from($mediaGalleryTable, 'value_id')
                ->where('value = ?', $picOrig[$key]['value']);
            $picId[$key] = $this->connection->fetchCol($selectPicId[$key]);
        }

        // Duplicate main entries of gallery
        foreach ($this->connection->fetchAll($select) as $row) {
            try {
                $imagePath = $this->copyImage($row['value']);
            } catch (FileSystemException $e) {
                $imgFilePath = $this->mediaConfig->getMediaPath($row['value']);
                $this->errors[] = __('Can not copy image file: %1', $imgFilePath);
                continue;
            }

            $data = [
                'attribute_id' => $mediaGalleryAttributeId,
                'value' => $imagePath,
            ];

            $this->connection->insert($mediaGalleryTable, $data);
            $valueIdMap[$row['value_id']] = $this->connection->lastInsertId($mediaGalleryTable);

            $this->connection->insert($mediaGalleryValueToEntityTable, [
                'value_id' => $valueIdMap[$row['value_id']],
                $entityIdName => $newProductId
            ]);

            // compare old position of basic, small and thumb with current copied picture
            foreach ($imageAttributeIds as $key => $attributeId) {
                if (in_array($row['value_id'], $picId[$key])) {
                    $newPic[$key] = $imagePath;
                }
            }
        }

        if (!$valueIdMap) {
            return false;
        }
        // Duplicate per store gallery values
        $galleryValueTable = $this->resource->getTableName(Gallery::GALLERY_VALUE_TABLE);
        $select = $this->connection->select()
            ->from($galleryValueTable)
            ->where('value_id IN(?)', array_keys($valueIdMap));

        foreach ($this->connection->fetchAll($select) as $row) {
            $data = $row;
            $data['value_id'] = $valueIdMap[$row['value_id']];
            $data[$entityIdName] = $newProductId;
            unset($data['record_id']);
            $this->connection->insert($galleryValueTable, $data);
        }

        // update basic, small and thumb
        foreach ($imageAttributeIds as $key => $attributeId) {
            if ($newPic[$key] !== 'no_selection') {
                $data = ['value' => $newPic[$key]];
                $where = [
                    'attribute_id = ?' => $attributeId,
                    $entityIdName . ' = ?' => $newProductId
                ];
                $update = $this->connection->update($productVarcharTable, $data, $where);

                if (!$update && $storeId[$key] !== null) {
                    $dataToInsert = [
                        'attribute_id' => $attributeId,
                        $entityIdName => $newProductId,
                        'value' => $newPic[$key],
                        'store_id' => $storeId[$key]
                    ];
                    $this->connection->insert($productVarcharTable, $dataToInsert);
                }
            }
        }

        return true;
    }

    private function getUniqueFileName(string $file): string
    {
        if ($this->fileStorageDb->checkDbUsage()) {
            $destinationFile = $this->fileStorageDb->getUniqueFilename(
                $this->mediaConfig->getBaseMediaUrlAddition(),
                $file
            );
        } else {
            $destinationFile = $this->mediaDirectoryWrite->getAbsolutePath($this->mediaConfig->getMediaPath($file));
            $destinationFile = $this->mediaDirectoryWrite->getDriver()->getParentDirectory($file)
                . '/' . Uploader::getNewFileName($destinationFile);
        }

        return $destinationFile;
    }

    private function copyImage(string $file): string
    {
        if (!$this->mediaDirectoryWrite->isFile($this->mediaConfig->getMediaPath($file))) {
            throw new FileSystemException(__('Image not found.'));
        }

        $destinationFile = $this->getUniqueFileName($file);

        if ($this->fileStorageDb->checkDbUsage()) {
            $this->fileStorageDb->copyFile(
                $this->mediaDirectoryWrite->getAbsolutePath($this->mediaConfig->getMediaShortUrl($file)),
                $this->mediaConfig->getMediaShortUrl($destinationFile)
            );
            $this->mediaDirectoryWrite->delete($this->mediaConfig->getMediaPath($destinationFile));
        } else {
            $this->mediaDirectoryWrite->copyFile(
                $this->mediaConfig->getMediaPath($file),
                $this->mediaConfig->getMediaPath($destinationFile)
            );
        }

        return str_replace('\\', '/', $destinationFile);
    }
}
