<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\Manager;
use Zend_Db_Expr;

class Inventory extends AbstractDb
{
    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var bool
     */
    private $msiEnabled = null;

    /**
     * @var array
     */
    private $sourceCodes;

    /**
     * @var array
     */
    private $stockStatus;

    /**
     * @var array
     */
    private $qty;

    /**
     * @var array
     */
    private $qtyBySource;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    public function __construct(
        Manager $moduleManager,
        StockRegistryInterface $stockRegistry,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->stockIds = [];
        $this->sourceCodes = [];
        $this->stockStatus = [];
        $this->qty = [];
        $this->qtyBySource = [];
    }

    /**
     * @param string[] $productSkus
     * @param string $websiteCode
     *
     * @throws NoSuchEntityException
     */
    public function loadStockStatus(array $productSkus, string $websiteCode): void
    {
        if (!isset($this->stockStatus[$websiteCode])) {
            $this->stockStatus[$websiteCode] = [];
        }

        if (!array_diff($productSkus, array_keys($this->stockStatus[$websiteCode]))) {
            return;
        }

        if ($this->isMSIEnabled()) {
            $result = $this->getMsiSalable($productSkus, $websiteCode);
            $this->stockStatus[$websiteCode] = array_replace($this->stockStatus[$websiteCode], $result);
        } else {
            foreach ($productSkus as $productSku) {
                $this->saveStockStatusCache(
                    $productSku,
                    $websiteCode,
                    $this->getStockItem($productSku, $websiteCode)->getIsInStock()
                );
            }
        }
    }

    private function saveStockStatusCache(string $productSku, string $websiteCode, int $stockStatus): void
    {
        $this->stockStatus[$websiteCode][$productSku] = $stockStatus;
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     * @return float
     *
     * @throws NoSuchEntityException
     */
    public function getQty(string $productSku, string $websiteCode): float
    {
        if ($this->isMSIEnabled()) {
            $qty = $this->getMsiQty($productSku, $websiteCode);
        } else {
            $qty = $this->getStockItem($productSku, $websiteCode)->getQty();
        }

        return (float) $qty;
    }

    private function isMSIEnabled(): bool
    {
        if ($this->msiEnabled === null) {
            $this->msiEnabled = $this->moduleManager->isEnabled('Magento_Inventory');
        }

        return $this->msiEnabled;
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return StockItemInterface
     *
     * @throws NoSuchEntityException
     */
    private function getStockItem(string $productSku, string $websiteCode): ?StockItemInterface
    {
        return $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
    }

    /**
     * For MSI. Need to get negative qty.
     * Emulate \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     *
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return float
     */
    public function getMsiQty(string $productSku, string $websiteCode): float
    {
        if (!isset($this->qty[$websiteCode][$productSku])) {
            $this->qty[$websiteCode][$productSku] = $this->getItemQty($productSku, $this->getSourceCodes($websiteCode))
                + $this->getReservationQty($productSku, $this->getStockId($websiteCode));
        }

        return (float) $this->qty[$websiteCode][$productSku];
    }

    /**
     * @param string[] $productSkus
     * @param string $websiteCode
     * @return string[]
     */
    public function getMsiSalable(array $productSkus, string $websiteCode): array
    {
        $stockId = $this->getStockId($websiteCode);
        if ($stockId === Stock::DEFAULT_STOCK_ID) {
            $table = 'cataloginventory_stock_status';
            $column = 'stock_status';
            $joinCondition = [
                ['cpe' => $this->getTable('catalog_product_entity')],
                'stock.product_id = cpe.entity_id',
                []
            ];
        } else {
            $table = sprintf('inventory_stock_%d', $stockId);
            $column = 'is_salable';
        }

        $select = $this->getConnection()->select()->from(
            ['stock' => $this->getTable($table)],
            [new Zend_Db_Expr('sku'), $column]
        )->where('sku IN (?)', $productSkus);

        if (isset($joinCondition)) {
            $select->join(...$joinCondition);
        }

        return (array) $this->getConnection()->fetchPairs($select);
    }

    private function getItemQty(string $productSku, array $sourceCodes): float
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_source_item'), ['SUM(quantity)'])
            ->where('source_code IN (?)', $sourceCodes)
            ->where('sku = ?', $productSku)
            ->group('sku');

        return (float) $this->getConnection()->fetchOne($select);
    }

    public function getItemQtyBySource(string $productSku, string $sourceCode): float
    {
        if (!isset($this->qtyBySource[$sourceCode][$productSku])) {
            $this->qtyBySource[$sourceCode][$productSku] = $this->getItemQty($productSku, [$sourceCode]);
        }

        return (float) $this->qtyBySource[$sourceCode][$productSku];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    public function getSourceCodes(string $websiteCode): array
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getStockId($websiteCode));

            $this->sourceCodes[$websiteCode] = $this->getConnection()->fetchCol($select);
        }

        return (array) $this->sourceCodes[$websiteCode];
    }

    private function getReservationQty(string $sku, int $stockId): float
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_reservation'), ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku)
            ->where('stock_id = ?', $stockId)
            ->limit(1);

        $reservationQty = $this->getConnection()->fetchOne($select);

        if ($reservationQty === false) {
            $reservationQty = 0.;
        }

        return (float) $reservationQty;
    }
}
