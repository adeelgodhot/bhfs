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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Changeattributeset extends Command
{
    const TYPE = 'changeattributeset';

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    public function __construct(
        Config $eavConfig,
        ProductRepositoryInterface $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        EntityResolver $entityResolver,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        $this->eavConfig = $eavConfig;
        $this->productRepository = $productRepository;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->entityResolver = $entityResolver;
        $this->getProductCollectionByIds = $getProductCollectionByIds;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Change Attribute Set')->render(),
            'confirm_message' => __('Are you sure you want to change attribute set?')->render(),
            'type' => $this->type,
            'label' => __('Change Attribute Set')->render(),
            'fieldLabel' => __('To')->render(),
            'placeholder' => __('Attribute Set Id')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $fromId = (int)trim($val);

        if (!$fromId) {
            throw new LocalizedException(__('Please provide a valid Attribute Group ID'));
        }
        $attributeSet = $this->attributeSetRepository->get($fromId);
        $productEntityId = $this->eavConfig->getEntityType(Product::ENTITY)->getId();

        if ($attributeSet->getEntityTypeId() != $productEntityId) {
            throw new LocalizedException(__('Provided Attribute set non product Attribute set.'));
        }
        $num = $configurable = 0;
        $entityIdName = $this->entityResolver->getEntityLinkField(ProductInterface::class);

        foreach ($this->getProductCollectionByIds->get($ids, $entityIdName) as $product) {
            try {
                if ($product->getTypeId() == Configurable::TYPE_CODE) {
                    $configurable++;
                } else {
                    $product->setAttributeSetId($fromId)
                        ->setStoreId($storeId)
                        ->setIsMassupdate(true);
                    $this->productRepository->save($product);
                    ++$num;
                }
            } catch (\Exception $e) {
                $this->errors[] = __(
                    'Can not change the attribute set for product ID %1, error is: %2',
                    $product->getId(),
                    $e->getMessage()
                );
            }
        }

        if ($configurable) {
            $this->errors[] = __(
                'Total of %1 products(s) have not been updated, the reason: '
                . 'impossibility to change attribute set for configurable product',
                $configurable
            );
        }

        return __('Total of %1 products(s) have been successfully updated.', $num);
    }
}
