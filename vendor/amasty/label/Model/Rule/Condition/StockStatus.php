<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Rule\Condition;

use Amasty\Label\Model\Source\Rules\Operator\BooleanOptions;
use Amasty\Label\Model\Source\Rules\Value\StockStatus as StockStatusOptionsProvider;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\DB\Select;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class StockStatus extends AbstractCondition
{
    /**
     * @var BooleanOptions
     */
    private $booleanOptions;

    /**
     * @var StockStatusOptionsProvider
     */
    private $stockStatusOptionsProvider;

    /**
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        ModuleManager $moduleManager,
        BooleanOptions $booleanOptions,
        StockStatusResource $stockStatusResource,
        StockStatusOptionsProvider $stockStatusOptionsProvider,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->stockStatusOptionsProvider = $stockStatusOptionsProvider;
        $this->stockStatusResource = $stockStatusResource;
        $this->booleanOptions = $booleanOptions;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;

        parent::__construct(
            $context,
            $data
        );
    }

    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        $select = $collection->getSelect();

        if (!$this->isStockStatusJoined($select)) {
            $this->stockStatusResource->addStockStatusToSelect($select, $this->storeManager->getWebsite());
            $this->prepareSelect($select);
        }
    }

    private function prepareSelect(Select $select): void
    {
        $catalogInventoryTable = $this->stockStatusResource->getMainTable();
        $fromTables = $select->getPart(Select::FROM);

        if ($this->isMsiEnabled() && $fromTables['stock_status']['tableName'] === $catalogInventoryTable) {
            $fromTables['stock_status']['joinCondition'] = preg_replace(
                '@(?<=stock_status\.website_id=)\d+@',
                Store::DEFAULT_STORE_ID,
                $fromTables['stock_status']['joinCondition']
            );
            $select->setPart(Select::FROM, $fromTables);
        }
    }

    private function isStockStatusJoined($select): bool
    {
        $fromTables = $select->getPart(Select::FROM);

        return isset($fromTables['stock_status']);
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model): bool
    {
        $validatedValue = $model->hasData('is_salable')
            ? $model->getData('is_salable') : $model->getData('stock_status');

        return $this->validateAttribute($validatedValue);
    }

    private function isMsiEnabled(): bool
    {
        return $this->moduleManager->isEnabled('Magento_Inventory');
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Stock Status');
    }

    public function getInputType(): string
    {
        return 'select';
    }

    public function getValueElementType(): string
    {
        return 'select';
    }

    public function getOperatorSelectOptions(): array
    {
        return $this->booleanOptions->toOptionArray();
    }

    public function getValueSelectOptions(): array
    {
        return $this->stockStatusOptionsProvider->toOptionArray();
    }
}
