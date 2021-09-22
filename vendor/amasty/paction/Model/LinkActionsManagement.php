<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Model\Product\Link;
use Magento\Catalog\Model\Product\Link\SaveHandler;

class LinkActionsManagement
{
    /**
     * @var ProductLinkInterfaceFactory
     */
    private $productLinkInterfaceFactory;

    /**
     * @var SaveHandler
     */
    private $saveProductLinks;

    public function __construct(
        ProductLinkInterfaceFactory $productLinkInterfaceFactory,
        SaveHandler $saveProductLinks
    ) {
        $this->productLinkInterfaceFactory = $productLinkInterfaceFactory;
        $this->saveProductLinks = $saveProductLinks;
    }

    public function createNewLink(ProductInterface $mainProduct, ProductInterface $linkedProduct, string $type): void
    {
        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLinks */
        $productLinks = $this->productLinkInterfaceFactory->create();
        $linkDataAll = $mainProduct->getProductLinks();

        $linkData = $productLinks
            ->setSku($mainProduct->getSku())
            ->setLinkedProductSku($linkedProduct->getSku())
            ->setLinkType($type);
        $linkDataAll[] = $linkData;
        $mainProduct->setProductLinks($linkDataAll);
        $this->saveProductLinks->execute(ProductInterface::class, $mainProduct);
    }

    public function getLinkTypeId(string $action): ?int
    {
        $types = [
            'copycrosssell' => Link::LINK_TYPE_CROSSSELL,
            'crosssell' => Link::LINK_TYPE_CROSSSELL,
            'uncrosssell' => Link::LINK_TYPE_CROSSSELL,
            'copyupsell' => Link::LINK_TYPE_UPSELL,
            'upsell' => Link::LINK_TYPE_UPSELL,
            'unupsell' => Link::LINK_TYPE_UPSELL,
            'copyrelate' => Link::LINK_TYPE_RELATED,
            'related' => Link::LINK_TYPE_RELATED,
            'unrelated' => Link::LINK_TYPE_RELATED
        ];

        return $types[$action] ?? null;
    }

    public function getLinkType(string $action): ?string
    {
        $types = [
            'copycrosssell' => 'crosssell',
            'crosssell' => 'crosssell',
            'uncrosssell' => 'crosssell',
            'copyupsell' => 'upsell',
            'upsell' => 'upsell',
            'unupsell' => 'upsell',
            'copyrelate' => 'related',
            'related' => 'related',
            'unrelate' => 'related'
        ];

        return $types[$action] ?? null;
    }
}
