<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\Pack\Finder\GetItemId;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPoolFactory;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack as ComplexPackResult;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPackFactory as ComplexPackResultFactory;
use Amasty\Mostviewed\Model\Pack\Finder\RetrievePackFromPool;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class GetAppliedPacks
{
    /**
     * @var array
     */
    private $appliedPacks = [];

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    /**
     * @var ItemPoolFactory
     */
    private $itemPoolFactory;

    /**
     * @var RetrievePackFromPool
     */
    private $retrievePackFromPool;

    /**
     * @var ComplexPackResultFactory
     */
    private $complexPackResultFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetItemId
     */
    private $getItemId;

    public function __construct(
        PackRepositoryInterface $packRepository,
        GroupValidator $groupValidator,
        RetrievePackFromPool $retrievePackFromPool,
        ItemPoolFactory $itemPoolFactory,
        ComplexPackResultFactory $complexPackResultFactory,
        ConfigProvider $configProvider,
        GetItemId $getItemId
    ) {
        $this->packRepository = $packRepository;
        $this->groupValidator = $groupValidator;
        $this->retrievePackFromPool = $retrievePackFromPool;
        $this->itemPoolFactory = $itemPoolFactory;
        $this->complexPackResultFactory = $complexPackResultFactory;
        $this->configProvider = $configProvider;
        $this->getItemId = $getItemId;
    }

    /**
     * @param CartInterface $quote
     * @return PackResult[]
     */
    public function execute(CartInterface $quote): array
    {
        if (!isset($this->appliedPacks[$quote->getId()])) {
            $itemPool = $this->convertQuoteToPool($quote);
            $this->appliedPacks[$quote->getId()] = $this->resolvePacks($itemPool, (int) $quote->getStoreId());
        }

        return $this->appliedPacks[$quote->getId()];
    }

    /**
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return ComplexPackResult[]
     */
    private function resolvePacks(ItemPool $itemPool, int $storeId): array
    {
        $appliedPacks = [];
        foreach ($this->findAllAvailablePacks($itemPool, $storeId) as $pack) {
            /** @var ComplexPackResult $complexPackResult */
            $complexPackResult = $this->complexPackResultFactory->create();
            $packResults = $this->retrievePackFromPool->execute($pack, $itemPool);
            $complexPackResult->setPacks($packResults);
            $complexPackResult->setPackId((int) $pack->getPackId());
            if ($complexPackResult->getPackQty()) {
                $appliedPacks[] = $complexPackResult;
            }
        }

        return $appliedPacks;
    }

    /**
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return PackInterface[]
     */
    private function findAllAvailablePacks(ItemPool $itemPool, int $storeId): array
    {
        $allProductIds = [];
        foreach ($itemPool->getItems() as $item) {
            $allProductIds[] = $item->getProductId();
        }
        $allProductIds = array_unique($allProductIds);

        $packsAsChild = $this->packRepository->getPacksByChildProductsAndStore($allProductIds, $storeId) ?: [];
        $packsAsParent = $this->packRepository->getPacksByParentProductsAndStore($allProductIds, $storeId) ?: [];

        /** @var PackInterface[] $packsMerged */
        $packsMerged = [];
        foreach (array_merge($packsAsChild, $packsAsParent) as $pack) {
            if ($this->groupValidator->validate($pack)) {
                $packsMerged[$pack->getPackId()] = $pack;
            }
        }
        usort($packsMerged, function ($packA, $packB) {
            return $packA->getPackId() > $packB->getPackId();
        });

        return $packsMerged;
    }

    private function convertQuoteToPool(CartInterface $quote): ItemPool
    {
        /** @var ItemPool $itemPool */
        $itemPool = $this->itemPoolFactory->create();

        /** @var QuoteItem $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            if ($this->configProvider->isProductsCanBeAddedSeparately()
                || $quoteItem->getOptionByCode(AddProductsByIds::BUNDLE_PACK_OPTION_CODE)
            ) {
                $this->updateQuoteItem($quoteItem);
                $itemPool->createItem(
                    (int) $quoteItem->getAmBundleItemId(),
                    (int) $quoteItem->getProduct()->getId(),
                    (float) $quoteItem->getTotalQty()
                );
            }
        }

        return $itemPool;
    }

    private function updateQuoteItem(QuoteItem $item): void
    {
        // we need unique identifier for quote item,
        // but quote item id is null on first totals collect after adding product(bundle pack) in cart
        $item->setAmBundleItemId($item->getId() ?? $this->getItemId->execute());
    }
}
