<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\EntityResolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\App\ResourceConnection;

class Removecategory extends Addcategory
{
    const TYPE = 'removecategory';

    public function __construct(
        ResourceConnection $resource,
        Category\CollectionFactory $categoryCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        EntityResolver $entityResolver
    ) {
        parent::__construct($resource, $categoryCollectionFactory, $categoryRepository, $entityResolver);

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Remove Category')->render(),
            'confirm_message' => __('Are you sure you want to remove category?')->render(),
            'type' => $this->type,
            'label' => __('Remove Category')->render(),
            'fieldLabel' => __('Category IDs')->render(),
            'placeholder' => __('id1,id2,id3')->render()
        ];
    }
}
