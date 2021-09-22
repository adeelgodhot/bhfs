<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\Data\LabelTooltipInterface;
use Amasty\Label\Model\ResourceModel\Label;
use Amasty\Label\Model\ResourceModel\Label\Collection;
use Amasty\Label\Model\Rule\Condition\Combine as AmastyCombineCondition;
use Amasty\Label\Model\Rule\Condition\IsNew;
use Amasty\Label\Model\Rule\Condition\OnSale as OnSaleRule;
use Amasty\Label\Model\Rule\Condition\Qty;
use Amasty\Label\Model\Rule\Condition\StockStatus;
use Amasty\Label\Model\Source\Rules\Operator\OnSale;
use Amasty\Label\Model\Source\TooltipStatus;
use Amasty\Label\Setup\Uninstall;
use Magento\CatalogRule\Model\Rule\Condition\Combine as MagentoCombibeCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Customer\Model\Group;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Store\Model\Store;
use Throwable as Throwable;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConvertFlatLabelDataToStructuredView
{
    const LABEL_ENTITY_DATA_MAP = [
        LabelInterface::LABEL_ID => 'label_id',
        LabelInterface::PRIORITY => 'pos',
        LabelInterface::STATUS => 'status',
        LabelInterface::NAME => 'name',
        LabelInterface::ACTIVE_FROM => 'from_date',
        LabelInterface::ACTIVE_TO => 'to_date',
        LabelInterface::USE_FOR_PARENT => 'use_for_parent',
        LabelInterface::IS_SINGLE => 'is_single'
    ];

    const CATALOG_PART_DATA_MAP = [
        LabelFrontendSettingsInterface::LABEL_TEXT => 'txt',
        LabelFrontendSettingsInterface::IMAGE => 'img',
        LabelFrontendSettingsInterface::POSITION => 'pos',
        LabelFrontendSettingsInterface::STYLE => 'style',
        LabelFrontendSettingsInterface::IMAGE_SIZE => 'image_size'
    ];

    const EMPTY_COMBINE_CONDITION = [
        'type' => AmastyCombineCondition::class,
        'attribute' => null,
        'operator' => null,
        'value' => 1,
        'is_value_processed' => null,
        'aggregator' => 'all'
    ];

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Timezone
     */
    private $timezone;

    public function __construct(
        Serializer $serializer,
        Timezone $timezone
    ) {
        $this->serializer = $serializer;
        $this->timezone = $timezone;
    }

    public function convert(array $flatLabel): array
    {
        $labelId = $flatLabel[LabelInterface::LABEL_ID] ?? 0;
        $result = [];

        if ($labelId) {
            $labelId = (int) $labelId;
            $partMapping = ['cat' => Collection::MODE_LIST, 'prod' => Collection::MODE_PDP];
            $labelData = [
                Label::TABLE_NAME => $this->extractLabelEntityData($flatLabel),
                Uninstall::AMASTY_LABEL_STORE_TABLE => $this->extractStoresData($labelId, $flatLabel),
                Uninstall::AMASTY_LABEL_CUSTOMER_GROUP_TABLE => $this->extractCustomerGroupsData($labelId, $flatLabel),
                Uninstall::AMASTY_LABEL_TOOLTIP_TABLE => [
                    LabelInterface::LABEL_ID => $labelId,
                    LabelTooltipInterface::STATUS => TooltipStatus::DISABLED
                ]
            ];

            foreach ($partMapping as $partPrefix => $partTypeCode) {
                $result[$partTypeCode] = $labelData;
                $result[$partTypeCode][Uninstall::AMASTY_LABEL_CATALOG_PARTS_TABLE] = $this->extractCatalogPartData(
                    $partPrefix,
                    $partTypeCode,
                    $labelId,
                    $flatLabel
                );
            }
        } else {
            throw new \RuntimeException(__('Invalid label data')->render());
        }

        return $result;
    }

    private function prepareConditionSerialized(array $flatLabel): string
    {
        $conditions = $this->parseConditions($flatLabel['cond_serialize'] ?? null);
        $conditions = $this->replaceMagentoCombineClass($conditions);
        $additionalConditions = [];
        $this->processIsNewCondition($additionalConditions, $flatLabel);
        $this->processOnSaleCondition($additionalConditions, $flatLabel);
        $this->processStockStatusCondition($additionalConditions, $flatLabel);
        $this->processStockRanges($additionalConditions, $flatLabel);
        $this->processPriceRanges($additionalConditions, $flatLabel);

        if (!empty($additionalConditions)) {
            if (empty($conditions['conditions'])) {
                $conditions['conditions'] = $additionalConditions;
            } else {
                $additionalConditionsCombine = self::EMPTY_COMBINE_CONDITION;
                $additionalConditionsCombine['conditions'] = $additionalConditions;
                $conditions['conditions'][] = $additionalConditionsCombine;
            }
        }

        return $this->serializer->serialize($conditions);
    }

    private function processPriceRanges(array &$additionalConditions, array $flatData): void
    {
        $isPriceRangeEnable = boolval($flatData['price_range_enabled'] ?? false);
        $priceRangeFrom = $flatData['from_price'] ?? null;
        $priceRangeTo = $flatData['to_price'] ?? null;

        if ($isPriceRangeEnable && ($priceRangeFrom !== null || $priceRangeTo !== null)) {
            $priceConditions = [];

            if ($priceRangeFrom !== null) {
                $priceConditions[] = [
                    'type' => Product::class,
                    'attribute' => 'price',
                    'operator' => '>=',
                    'value' => (double) $priceRangeFrom,
                    'is_value_processed' => false,
                ];
            }

            if ($priceRangeTo !== null) {
                $priceConditions[] = [
                    'type' => Product::class,
                    'attribute' => 'price',
                    'operator' => '<=',
                    'value' => (double) $priceRangeTo,
                    'is_value_processed' => false,
                ];
            }

            if (count($priceConditions) > 1) {
                $combineCondition = self::EMPTY_COMBINE_CONDITION;
                $combineCondition['conditions'] = array_merge(
                    $combineCondition['conditions'] ?? [],
                    $priceConditions
                );
                $additionalConditions[] = $combineCondition;
            } else {
                $additionalConditions[] = reset($priceConditions);
            }
        }
    }

    private function processStockRanges(array &$additionalConditions, array $flatData): void
    {
        $isStockRangesEnabled = boolval($flatData['product_stock_enabled'] ?? false);
        $stockFrom = $flatData['stock_higher'] ?? null;
        $stockTo = $flatData['stock_less'] ?? null;

        if ($isStockRangesEnabled && ($stockFrom !== null || $stockTo !== null)) {
            $stockConditions = [];

            if ($stockFrom !== null) {
                $stockConditions[] = [
                    'type' => Qty::class,
                    'attribute' => null,
                    'operator' => '>=',
                    'value' => (int) $stockFrom,
                    'is_value_processed' => false,
                ];
            }

            if ($stockTo !== null) {
                $stockConditions[] = [
                    'type' => Qty::class,
                    'attribute' => null,
                    'operator' => '<=',
                    'value' => (int) $stockTo,
                    'is_value_processed' => false,
                ];
            }

            if (count($stockConditions) > 1) {
                $combineCondition = self::EMPTY_COMBINE_CONDITION;
                $combineCondition['conditions'] = array_merge(
                    $combineCondition['conditions'] ?? [],
                    $stockConditions
                );
                $additionalConditions[] = $combineCondition;
            } else {
                $additionalConditions[] = reset($stockConditions);
            }
        }
    }

    private function processStockStatusCondition(array &$additionalConditions, array $flatData): void
    {
        $stockStatus = $flatData['stock_status'] ?? 0;
        $stockStatus = (int) $stockStatus;

        if ($stockStatus !== 0) {
            $additionalConditions[] = [
                'type' => StockStatus::class,
                'attribute' => null,
                'operator' => '==',
                'value' => $stockStatus === 1 ? '0' : '1',
                'is_value_processed' => false,
            ];
        }
    }

    private function processOnSaleCondition(array &$additionalConditions, array $flatData): void
    {
        $isOnSale = $flatData['is_sale'] ?? 0;
        $isOnSale = (int) $isOnSale;
        $useSpecialPriceOnly = boolval($flatData['special_price_only'] ?? false);

        if ($isOnSale !== 0) {
            $operator = $useSpecialPriceOnly ? OnSale::FOR_SPECIAL_PRICE_ONLY : '==';

            if ($useSpecialPriceOnly) {
                $value = null;
            } else {
                $value = $isOnSale === 1 ? '0' : '1';
            }

            $additionalConditions[] = [
                'type' => OnSaleRule::class,
                'attribute' => null,
                'operator' => $operator,
                'value' => $value,
                'is_value_processed' => false,
            ];
        }
    }

    private function processIsNewCondition(array &$additionalConditions, array $flatData): void
    {
        $isNew = $flatData['is_new'] ?? 0;
        $isNew = (int) $isNew;

        if ($isNew !== 0) {
            $additionalConditions[] = [
                'type' => IsNew::class,
                'attribute' => null,
                'operator' => '==',
                'value' => $isNew === 1 ? '0' : '1',
                'is_value_processed' => false,
            ];
        }
    }

    private function replaceMagentoCombineClass(array $conditions): array
    {
        if (isset($conditions['type']) && $conditions['type'] === MagentoCombibeCondition::class) {
            $conditions['type'] = AmastyCombineCondition::class;

            if (!empty($conditions['conditions']) && is_array($conditions['conditions'])) {
                foreach ($conditions['conditions'] as $key => $condition) {
                    $conditions['conditions'][$key] = $this->replaceMagentoCombineClass($condition);
                }
            }
        }

        return $conditions;
    }

    private function parseConditions(?string $conditionsSerialized): array
    {
        $conditions = self::EMPTY_COMBINE_CONDITION;

        if ($conditionsSerialized !== '' && $conditionsSerialized !== null) {
            try {
                $conditions = $this->serializer->unserialize($conditionsSerialized);
            } catch (Throwable $e) {
                $conditions = self::EMPTY_COMBINE_CONDITION;
            }
        }

        return $conditions;
    }

    private function extractCatalogPartData(string $partPrefix, int $typeCode, int $labelId, array $flatLabel): array
    {
        $result = [
            LabelInterface::LABEL_ID => $labelId,
            LabelFrontendSettingsInterface::TYPE => $typeCode
        ];

        foreach (self::CATALOG_PART_DATA_MAP as $newKey => $oldKeyPart) {
            $oldKey = "{$partPrefix}_{$oldKeyPart}";

            if (array_key_exists($oldKey, $flatLabel)) {
                $result[$newKey] = $flatLabel[$oldKey];
            }
        }

        return $result;
    }

    private function extractCustomerGroupsData(int $labelId, array $flatData): array
    {
        $customerGroups = $flatData['customer_group_ids'] ?? '';
        $customerGroupsEnabled = $flatData['customer_group_enabled'] ?? false;
        $result = [];

        if ($customerGroupsEnabled && $customerGroups !== '' && $customerGroups !== null) {
            try {
                $customerGroupIds = (array) $this->serializer->unserialize($customerGroups);

                if (!empty($customerGroupIds)) {
                    $result = array_reduce($customerGroupIds, function ($carry, $customerGroup) use ($labelId) {
                        $carry[] = [
                            LabelInterface::LABEL_ID => $labelId,
                            Collection::CUSTOMER_GROUP_ID => (int) $customerGroup
                        ];

                        return $carry;
                    }, $result);
                }
            } catch (Throwable $e) {
                $result = [];
            }
        } else {
            $result[] = [
                LabelInterface::LABEL_ID => $labelId,
                Collection::CUSTOMER_GROUP_ID => Group::CUST_GROUP_ALL
            ];
        }

        return $result;
    }

    private function extractStoresData(int $labelId, array $flatData): array
    {
        $result = [];
        $storeIds = $flatData['stores'] ?? '';

        if ($storeIds !== '' && $storeIds !== null) {
            $storeIds = array_map('intval', explode(',', $storeIds));
            $result = array_reduce($storeIds, function (array $carry, int $storeId) use ($labelId): array {
                $carry[] = [
                    LabelInterface::LABEL_ID => $labelId,
                    Collection::STORE_ID_FIELD => $storeId
                ];

                return $carry;
            }, $result);
        } else {
            $result[] = [
                LabelInterface::LABEL_ID => $labelId,
                Collection::STORE_ID_FIELD => Store::DEFAULT_STORE_ID
            ];
        }

        return $result;
    }

    private function extractLabelEntityData(array $flatData): array
    {
        $result = [];

        foreach (self::LABEL_ENTITY_DATA_MAP as $newColumnName => $oldColumnName) {
            if (array_key_exists($oldColumnName, $flatData)) {
                $result[$newColumnName] = $flatData[$oldColumnName];
            }
        }

        $result[LabelInterface::CONDITION_SERIALIZED] = $this->prepareConditionSerialized($flatData);

        foreach ([LabelInterface::ACTIVE_FROM, LabelInterface::ACTIVE_TO] as $dateTimeKey) {
            //If datetime field has zero value
            if (!empty($result[$dateTimeKey]) && preg_match('/^[\s0\-:]+$/m', $result[$dateTimeKey])) {
                $result[$dateTimeKey] = null;
            } elseif (!empty($result[$dateTimeKey])) {
                $result[$dateTimeKey] = $this->timezone->convertConfigTimeToUtc($result[$dateTimeKey]);
            }
        }

        return $result;
    }
}
