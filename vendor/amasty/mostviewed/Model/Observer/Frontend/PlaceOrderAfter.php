<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Observer\Frontend;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Analytic\AppendPackSales as AppendPackAnalyticSales;
use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetAppliedPacks;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Model\Pack\Sales\AppendPackSales;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales\Table;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

class PlaceOrderAfter implements ObserverInterface
{
    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var AppendPackAnalyticSales
     */
    private $appendPackAnalyticSales;

    /**
     * @var AppendPackSales
     */
    private $appendPackSales;

    /**
     * @var GetAppliedPacks
     */
    private $getAppliedPacks;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        PackRepositoryInterface $packRepository,
        AppendPackAnalyticSales $appendPackAnalyticSales,
        AppendPackSales $appendPackSales,
        GetAppliedPacks $getAppliedPacks,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->packRepository = $packRepository;
        $this->appendPackAnalyticSales = $appendPackAnalyticSales;
        $this->appendPackSales = $appendPackSales;
        $this->getAppliedPacks = $getAppliedPacks;
        $this->priceCurrency = $priceCurrency;
    }

    public function execute(Observer $observer)
    {
        /** @var CartInterface|Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();

        if ($quote && $order) {
            $data = [];
            $salesData = [];
            $appliedPacks = $this->getAppliedPacks->execute($quote);
            foreach ($appliedPacks as $appliedPack) {
                try {
                    $pack = $this->packRepository->getById(
                        $appliedPack->getPackId()
                    ); // packs already load in RulesApplier
                    $data[$pack->getPackId()] = [
                        'name' => $pack->getName(),
                        'qty' => $appliedPack->getPackQty()
                    ];
                    foreach ($appliedPack->getPacks() as $simplePack) {
                        $packSalesData = $this->getPackSaleData($simplePack, $quote, $pack);
                        $packSalesData[Table::ORDER_ID_COLUMN] = (int) $order->getEntityId();
                        array_push(
                            $salesData,
                            ...array_fill(0, $simplePack->getPackQty(), $packSalesData)
                        );
                    }
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }

            $this->appendPackAnalyticSales->execute((int) $order->getEntityId(), $data);
            $this->appendPackSales->execute($salesData);
        }
    }

    private function getPackSaleData(SimplePack $simplePack, CartInterface $quote, PackInterface $pack): array
    {
        $productNames = '';
        $baseGrandTotal = 0;
        $grandTotal = 0;
        foreach ($simplePack->getItems() as $itemId => $itemQty) {
            if ($item = $quote->getItemById($itemId)) {
                $preparedProductName = sprintf('%s, ', $item->getProduct()->getName());
                if (in_array($item->getProduct()->getId(), $pack->getParentIds())) {
                    $productNames = $preparedProductName . $productNames;
                } else {
                    $productNames .= $preparedProductName;
                }
                $itemQtyPerPack = $simplePack->getItemQty($itemId) / $simplePack->getPackQty();
                $discounts = $item->getAmDiscounts()[$simplePack->getId()] ?? [];
                if (empty($discounts)) {
                    continue;
                }
                $total = $this->priceCurrency->round($item->getCalculationPriceOriginal())
                    * $itemQtyPerPack;
                $baseTotal = $this->priceCurrency->round($item->getBaseCalculationPriceOriginal())
                    * $itemQtyPerPack;
                $baseGrandTotal += $baseTotal - $discounts['base_amount'] / $simplePack->getPackQty();
                $grandTotal += $total - $discounts['amount'] / $simplePack->getPackQty();
            }
        }
        $productNames = rtrim($productNames, ', ');

        return [
            Table::PACK_NAME_COLUMN => $pack->getName(),
            Table::PACK_ID_COLUMN => $pack->getPackId(),
            Table::PRODUCT_NAMES_COLUMN => $productNames,
            Table::TOTAL_ID_COLUMN => $grandTotal,
            Table::BASE_TOTAL_ID_COLUMN => $baseGrandTotal
        ];
    }
}
