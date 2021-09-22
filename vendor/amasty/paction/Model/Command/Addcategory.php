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
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Addcategory extends Command
{
    const TYPE = 'addcategory';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Category\CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    public function __construct(
        ResourceConnection $resource,
        Category\CollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        EntityResolver $entityResolver
    ) {
        $this->resource = $resource;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->entityResolver = $entityResolver;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Assign Category')->render(),
            'confirm_message' => __('Are you sure you want to assign category?')->render(),
            'type' => $this->type,
            'label' => __('Assign Category')->render(),
            'fieldLabel' => __('Category IDs')->render(),
            'placeholder' => __('id1,id2,id3')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $categoryIds): Phrase
    {
        $categoryIds = $this->entityResolver->getEntityLinkIds(
            CategoryInterface::class,
            $this->prepareCategoryIds($categoryIds)
        );

        if ($this->type === 'replacecategory') { // remove product(s) from all categories
            $table = $this->resource->getTableName('catalog_category_product');
            $this->resource->getConnection()->delete($table, ['product_id IN(?)' => $ids]);
            $this->type = 'addcategory';
        }

        $numAffectedCats  = 0;
        $allAffectedProducts = [];
        $categoryIdField = $this->entityResolver->getEntityLinkField(CategoryInterface::class);
        /** @var Category\Collection $categoriesCollection */
        $categoriesCollection = $this->categoryCollectionFactory->create();
        $categoriesCollection->addFieldToFilter($categoryIdField, ['in' => $categoryIds]);
        $categoriesCollection->addNameToResult();
        $categoriesCollection->addAttributeToSelect('is_anchor');
        $categoriesCollection->addAttributeToSelect('url_key');
        $categoriesCollection->setStoreId($storeId);

        /** @var CategoryInterface $category */
        foreach ($categoriesCollection as $category) {
            $positions = $category->getProductsPosition();
            $currentAffectedProducts = 0;

            foreach ($ids as $productId) {
                if ($this->type === 'addcategory' && !isset($positions[$productId])) { // add only new
                    $positions[$productId] = 0;
                    $allAffectedProducts[] = $productId;
                    $currentAffectedProducts++;
                } elseif ($this->type === 'removecategory' && isset($positions[$productId])) { //remove only existing
                    unset($positions[$productId]);
                    $allAffectedProducts[] = $productId;
                    $currentAffectedProducts++;
                }
            }

            if ($currentAffectedProducts) {
                $category->setPostedProducts($positions);
                $category->setData('use_default', ['url_key' => true]);
                try {
                    $category->save(); //category is reloaded in repository, loosing posted products
                    ++$numAffectedCats;
                    $allAffectedProducts = array_unique($allAffectedProducts);
                } catch (\Exception $e) {
                    $this->errors[] = __(
                        'Can not handle the category ID=%1, the error is: %2',
                        $category->getId(),
                        $e->getMessage()
                    );
                }
            }
        }

        return __(
            'Total of %1 category(ies) and %2 product(s) have been successfully updated.',
            $numAffectedCats,
            count($allAffectedProducts)
        );
    }

    protected function prepareCategoryIds(string $categoryIds): array
    {
        if (!$categoryIds) {
            throw new LocalizedException(__('Please provide comma separated category IDs'));
        }

        $validCategoryIds = $validationErrors = [];
        array_map(function ($categoryId) use (&$validCategoryIds, &$validationErrors) {
            if ((int)$categoryId <= 1) {
                $validationErrors[] = __('Magento2 does not allow to save the category ID=%1', $categoryId);
            } else {
                $validCategoryIds[] = $categoryId;
            }
        }, explode(',', $categoryIds));
        $this->errors = array_merge($this->errors, $validationErrors);

        return $validCategoryIds;
    }
}
