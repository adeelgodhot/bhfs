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
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class GetPacksForCartItem
{
    /**
     * @var GetAppliedPacks
     */
    private $getAppliedPacks;

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    public function __construct(
        GetAppliedPacks $getAppliedPacks,
        PackRepositoryInterface $packRepository
    ) {
        $this->getAppliedPacks = $getAppliedPacks;
        $this->packRepository = $packRepository;
    }

    /**
     * @param AbstractItem $item
     * @return ComplexPack[]
     * @throws NoSuchEntityException
     */
    public function execute(AbstractItem $item): array
    {
        $packsForItem = [];
        $productId = (int) $item->getProduct()->getId();
        foreach ($this->getAppliedPacks->execute($item->getQuote()) as $appliedPack) {
            $pack = $this->packRepository->getById($appliedPack->getPackId(), true);
            if ($this->isPackCanAppliedForProduct($pack, $productId)) {
                $packsForItem[] = $appliedPack;
            }
        }

        return $packsForItem;
    }

    private function isPackCanAppliedForProduct(PackInterface $pack, int $productId): bool
    {
        $childIds = explode(',', $pack->getProductIds());

        return in_array($productId, $childIds)
            || (
                in_array($productId, $pack->getParentIds())
                && ($pack->getApplyForParent() || $pack->getDiscountType() === DiscountType::CONDITIONAL)
            );
    }
}
