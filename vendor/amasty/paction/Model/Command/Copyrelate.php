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
use Amasty\Paction\Model\LinkActionsManagement;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Copyrelate extends Command
{
    const TYPE = 'copyrelate';

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var LinkActionsManagement
     */
    private $linkActionsManagement;

    /**
     * @var GetProductCollectionByIds
     */
    private $getProductCollectionByIds;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    public function __construct(
        ProductRepository $productRepository,
        LinkActionsManagement $linkActionsManagement,
        GetProductCollectionByIds $getProductCollectionByIds,
        EntityResolver $entityResolver
    ) {
        $this->productRepository = $productRepository;
        $this->linkActionsManagement = $linkActionsManagement;
        $this->getProductCollectionByIds = $getProductCollectionByIds;
        $this->entityResolver = $entityResolver;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Copy Relations')->render(),
            'confirm_message' => __('Are you sure you want to copy relations?')->render(),
            'type' => $this->type,
            'label' => __('Copy Relations')->render(),
            'fieldLabel' => __('From')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        $fromId = (int)trim($val);

        if (!$fromId) {
            throw new LocalizedException(__('Please provide a valid product ID'));
        }

        if (in_array($fromId, $ids)) {
            throw new LocalizedException(__('Please remove source product from the selected products'));
        }
        $relatedProducts = $this->getLinks($this->productRepository->getById($fromId));

        if (empty($relatedProducts)) {
            throw new LocalizedException(__('Source product has no relations'));
        }
        $mainProducts = $this->getProductCollectionByIds->get(
            $ids,
            $this->entityResolver->getEntityLinkField(ProductInterface::class)
        );
        $num = 0;

        foreach ($mainProducts as $mainProduct) {
            foreach ($relatedProducts as $relatedProduct) {
                if ($mainProduct->getId() === $relatedProduct->getId()) {
                    continue;
                }
                $this->linkActionsManagement->createNewLink(
                    $mainProduct,
                    $relatedProduct,
                    $this->linkActionsManagement->getLinkType($this->type)
                );
                $num++;
            }
        }

        if ($num === 1) {
            $success = __('Product association has been successfully added.');
        } else {
            $success = __('%1 product associations have been successfully added.', $num);
        }

        return $success;
    }

    protected function getLinks(ProductInterface $product): array
    {
        return $product->getRelatedProducts();
    }
}
