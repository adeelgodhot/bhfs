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
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Phrase;

class Changevisibility extends Command
{
    const TYPE = 'changevisibility';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var EntityResolver
     */
    private $entityResolver;
    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    public function __construct(
        ProductRepository $productRepository,
        EntityResolver $entityResolver,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->productRepository = $productRepository;
        $this->entityResolver = $entityResolver;
        $this->getProductCollectionByIds = $getProductCollectionByIds;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Change Visibility')->render(),
            'confirm_message' => __('Are you sure you want to change visibility?')->render(),
            'type' => $this->type,
            'label' => __('Change Visibility')->render(),
            'fieldLabel' => __('To')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $visibility = (int)trim($val);
        $num = 0;
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        foreach ($this->getProductCollectionByIds->get($ids, $entityIdName) as $product) {
            try {
                $product->setStoreId($storeId)->setVisibility($visibility);
                $product->save();
                ++$num;
            } catch (\Exception $e) {
                $this->errors[] = __(
                    'Can not change visibility for product ID %1, error is: %2',
                    $product->getId(),
                    $e->getMessage()
                );
            }
        }

        return __('Total of %1 products(s) have been successfully updated.', $num);
    }
}
