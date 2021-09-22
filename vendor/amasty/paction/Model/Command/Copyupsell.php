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
use Amasty\Paction\Model\LinkActionsManagement;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;

class Copyupsell extends Copyrelate
{
    const TYPE = 'copyupsell';

    public function __construct(
        ProductRepository $productRepository,
        LinkActionsManagement $linkActionsManagement,
        GetProductCollectionByIds $getProductCollectionByIds,
        EntityResolver $entityResolver
    ) {
        parent::__construct(
            $productRepository,
            $linkActionsManagement,
            $getProductCollectionByIds,
            $entityResolver
        );

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Copy Up-sells')->render(),
            'confirm_message' => __('Are you sure you want to copy up-sells?')->render(),
            'type' => $this->type,
            'label' => __('Copy Up-sells')->render(),
            'fieldLabel' => __('From')->render()
        ];
    }

    protected function getLinks(ProductInterface $product): array
    {
        return $product->getUpSellProducts();
    }
}
