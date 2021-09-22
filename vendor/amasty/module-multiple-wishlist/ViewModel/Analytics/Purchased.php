<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\ViewModel\Analytics;

use Amasty\MWishlist\Model\ResourceModel\Purchased\Grid\Collection;
use Amasty\MWishlist\Model\ResourceModel\Purchased\Grid\CollectionFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Purchased implements ArgumentInterface
{
    const TABLE_ROW_COUNT = 5;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->collectionFactory = $collectionFactory;
    }

    public function getPurchasedProducts(): Collection
    {
        $collection = $this->collectionFactory->create();
        $collection->addLimit(self::TABLE_ROW_COUNT);
        $collection->addOrder('qty', \Magento\Framework\Api\SortOrder::SORT_DESC);

        return $collection;
    }

    public function getMoreUrl(): string
    {
        return $this->urlBuilder->getUrl('mwishlist/item/purchased');
    }
}
