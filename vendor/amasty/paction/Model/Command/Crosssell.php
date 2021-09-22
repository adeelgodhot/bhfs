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
use Amasty\Paction\Model\GetProductCollectionByIds;
use Amasty\Paction\Model\LinkActionsManagement;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Link\SaveHandler;
use Magento\Framework\App\ResourceConnection;

class Crosssell extends Relate
{
    const TYPE = 'crosssell';

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SaveHandler $saveProductLinks,
        ResourceConnection $resource,
        ConfigProvider $configProvider,
        EntityResolver $entityResolver,
        LinkActionsManagement $linkActionsManagement,
        GetProductCollectionByIds $getProductCollectionByIds
    ) {
        parent::__construct(
            $productRepository,
            $saveProductLinks,
            $resource,
            $configProvider,
            $entityResolver,
            $linkActionsManagement,
            $getProductCollectionByIds
        );

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Cross-sell')->render(),
            'confirm_message' => __('Are you sure you want to cross-sell?')->render(),
            'type' => $this->type,
            'label' => __('Cross-sell')->render(),
            'fieldLabel' => __('Selected To IDs')->render(),
            'placeholder' => __('id1,id2,id3')->render()
        ];
        $this->setFieldLabel();
    }
}
