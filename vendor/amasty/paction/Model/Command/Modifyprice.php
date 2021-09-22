<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Command;

use Amasty\Paction\Model\Command;
use Amasty\Paction\Model\ConfigProvider;
use Amasty\Paction\Model\EntityResolver;
use Amasty\Paction\Model\Source\Rounding;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Modifyprice extends Command
{
    const TYPE = 'modifyprice';

    /**
     * Attribute code used as "source" attribute for price modification
     *
     * @var string
     */
    protected $sourceAttributeCode = 'price';

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var EntityResolver
     */
    protected $entityResolver;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        EntityResolver $entityResolver,
        ConfigProvider $configProvider
    ) {
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->connection = $resource->getConnection();
        $this->entityResolver = $entityResolver;
        $this->configProvider = $configProvider;

        $this->type = self::TYPE;
        $this->info = [
            'confirm_title' => __('Update Price')->render(),
            'confirm_message' => __('Are you sure you want to update price?')->render(),
            'type' => $this->type,
            'label' => __('Update Price')->render(),
            'fieldLabel' => __('By')->render(),
            'placeholder' => __('+12.5, -12.5, +12.5%')->render()
        ];
    }

    public function execute(array $ids, int $storeId, string $val): Phrase
    {
        if (!preg_match('/^[+-][0-9]+(\.[0-9]+)?%?$/', $val)) {
            throw new LocalizedException(__('Please provide the difference as +12.5, -12.5, +12.5% or -12.5%'));
        }
        $sign = substr($val, 0, 1);
        $val = substr($val, 1);
        $percent = ('%' == substr($val, -1, 1));

        if ($percent) {
            $val = (float)substr($val, 0, -1);
        }

        if ($val <= 0) {
            throw new LocalizedException(__('Please provide a non empty difference'));
        }
        $value = $this->prepareValue(['sign' => $sign, 'val' => $val, 'percent' => $percent], $storeId);
        $this->updateAttribute(
            $this->sourceAttributeCode,
            $ids,
            $storeId,
            $value
        );

        return __('Total of %1 products(s) have been successfully updated', count($ids));
    }

    protected function updateAttribute(string $attrCode, array $productIds, int $storeId, string $value): void
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attrCode);
        $table = $attribute->getBackend()->getTable();
        $entityIdName = $this->connection->quoteIdentifier(
            $this->entityResolver->getEntityLinkField(ProductInterface::class)
        );
        $where = [
            $this->connection->quoteInto($entityIdName . ' IN(?)', $productIds),
            $this->connection->quoteInto('attribute_id=?', $attribute->getAttributeId()),
        ];

        /**
         * If we work in single store mode all values should be saved just
         * for default store id. In this case we clear all not default values
         */
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if ($this->storeManager->isSingleStoreMode()) {
            $this->connection->delete(
                $table,
                implode(' AND ', array_merge($where, [$this->connection->quoteInto('store_id <> ?', $defaultStoreId)]))
            );
        }
        $storeIds  = [];

        if ($attribute->isScopeStore()) {
            $where[] = $this->connection->quoteInto('store_id = ?', $storeId);
            $storeIds[] = $storeId;
        } elseif ($attribute->isScopeWebsite() && $storeId != $defaultStoreId) {
            $storeIds = $this->storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            $where[] = $this->connection->quoteInto('store_id IN(?)', $storeIds);
        } else {
            $where[] = $this->connection->quoteInto('store_id = ?', $defaultStoreId);
        }

        // in case of store-view or website scope we need to insert default values
        // first, to be able to update them.
        if ($storeIds) {
            $cond = [
                $this->connection->quoteInto('t.' . $entityIdName . ' IN(?)', $productIds),
                $this->connection->quoteInto('t.attribute_id=?', $attribute->getAttributeId()),
                't.store_id = ' . (int)$defaultStoreId,
            ];

            foreach ($storeIds as $id) {
                $id = $this->connection->quote($id);
                $fields = ['value_id', 'attribute_id', 'store_id', new \Zend_Db_Expr($entityIdName), 'value'];
                $select = $this->connection->select()
                    ->from(['t' => $table])
                    ->reset('columns')
                    ->columns([
                        'value_id',
                        'attribute_id',
                        new \Zend_Db_Expr((int)$id),
                        new \Zend_Db_Expr($entityIdName),
                        'value'
                    ]);
                foreach ($cond as $part) {
                    $select->where($part);
                }
                $this->connection->query(
                    $this->connection->insertFromSelect(
                        $select,
                        $table,
                        $fields,
                        AdapterInterface::INSERT_IGNORE
                    )
                );
            }
        }
        $sql = $this->prepareQuery($table, $value, $where);
        $this->connection->query($sql);
    }

    protected function prepareValue(array $diff, int $storeId): string
    {
        $value = $diff['percent'] ? '`value` * ' . $diff['val'] . '/ 100' : $diff['val'];
        $value = '`value`' . $diff['sign'] . $value;
        $rounding = $this->configProvider->getPriceRoundingType($storeId);

        switch ($rounding) {
            case Rounding::FIXED:
                $fixed = $this->configProvider->getRoundingValue($storeId);

                if (!empty($fixed)) {
                    $fixed = (float)$fixed;
                    $value = 'FLOOR(' . $value . ') + ' . $fixed;
                }
                break;
            case Rounding::CROP:
                $value = 'TRUNCATE(' . $value . ',2)';
                break;
            case Rounding::NEAREST_INT:
                $value = 'ROUND(' . $value . ')';
                break;
            default: // Rounding::MATH
                $value = 'ROUND(' . $value . ',2)';
        }

        return $value;
    }

    protected function prepareQuery(string $table, string $value, array $where): string
    {
        $table = $this->connection->quoteIdentifier($table);
        $value = new \Zend_Db_Expr($value);
        // phpcs:ignore: Magento2.SQL.RawQuery.FoundRawSql
        return "UPDATE $table SET `value` = $value WHERE " . implode(' AND ', $where);
    }
}
