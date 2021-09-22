<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation\ToPlanMigration;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Config\Source\AmountType;
use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Config\Source\PlanStatus;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class PlansFormer
{
    const TYPE_CONFIG = 'config';
    const TYPE_PRODUCT = 'product';

    const LABEL_DAILY = 'Daily';
    const LABEL_WEEKLY = 'Weekly';
    const LABEL_MONTHLY = 'Monthly';
    const LABEL_ANNUAL = 'Annual';

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        StoreManagerInterface $storeManager
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function getDefaultPlansData(): array
    {
        $default = $this->getDefaultData();
        $listPlansData = [
            [
                SubscriptionPlanInterface::NAME => self::LABEL_DAILY,
                SubscriptionPlanInterface::FREQUENCY => 1,
                SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
            ],
            [
                SubscriptionPlanInterface::NAME => self::LABEL_WEEKLY,
                SubscriptionPlanInterface::FREQUENCY => 1,
                SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
            ],
            [
                SubscriptionPlanInterface::NAME => self::LABEL_MONTHLY,
                SubscriptionPlanInterface::FREQUENCY => 1,
                SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
            ],
            [
                SubscriptionPlanInterface::NAME => self::LABEL_ANNUAL,
                SubscriptionPlanInterface::FREQUENCY => 1,
                SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
            ],
        ];

        foreach ($listPlansData as &$planData) {
            foreach ($default as $key => $value) {
                !isset($planData[$key]) && $planData[$key] = $value;
            }
        }

        return $listPlansData;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return array
     */
    public function getPlansFromConfig(ModuleDataSetupInterface $setup): array
    {
        $connection = $setup->getConnection();
        $configTableName = $setup->getTable('core_config_data');
        $globalPathPrefix = Config::PATH_PREFIX . Config::GLOBAL_BLOCK;
        $select = $connection->select()
            ->from($configTableName)
            ->where('`path` IN(?)', [
                $globalPathPrefix . 'billing_cycle',
                $globalPathPrefix . 'billing_frequency',
                $globalPathPrefix . 'billing_frequency_unit',
                $globalPathPrefix . 'enable_free_trials',
                $globalPathPrefix . 'number_of_trial_days',
                $globalPathPrefix . 'charge_initial_fee',
                $globalPathPrefix . 'initial_fee_type',
                $globalPathPrefix . 'initial_fee_amount',
                $globalPathPrefix . 'discounted_prices',
                $globalPathPrefix . 'discount_type',
                $globalPathPrefix . 'discount_amount',
                $globalPathPrefix . 'discount_amount_percent',
                $globalPathPrefix . 'enable_limit_discounted_cycles',
                $globalPathPrefix . 'number_discounted_cycles',
            ]);

        $configRows = $connection->fetchAll($select);

        $configData = [];
        foreach ($configRows as $configRow) {
            $path = str_replace($globalPathPrefix, '', $configRow['path']);
            $configData[$configRow['scope'] . '_' . $configRow['scope_id']][$path] = $configRow['value'];
        }

        uksort($configData, function ($scopedKeyA, $scopedKeyB) {
            list($scopeA,) = explode('_', $scopedKeyA);
            list($scopeB,) = explode('_', $scopedKeyB);
            $scopesPriority = [
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT => 0,
                ScopeInterface::SCOPE_WEBSITES => 1,
                ScopeInterface::SCOPE_STORES => 2,
            ];

            return $scopesPriority[$scopeA] <=> $scopesPriority[$scopeB];
        });

        $storesToWebsites = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storesToWebsites[$store->getId()] = $store->getWebsiteId();
        }

        foreach ($configData as $scopeKey => $scopeData) {
            $defaultScopeKey = 'default_0';
            if ($scopeKey == $defaultScopeKey) {
                continue;
            }
            list($scope, $scopeId) = explode('_', $scopeKey);

            if ($scope == ScopeInterface::SCOPE_STORES && isset($storesToWebsites[$scopeId])) {
                $websiteData = $configData[ScopeInterface::SCOPE_WEBSITES . '_' . $storesToWebsites[$scopeId]] ?? [];
                foreach ($websiteData as $path => $value) {
                    if (!array_key_exists($path, $configData[$scopeKey])) {
                        $configData[$scopeKey][$path] = $value;
                    }
                }
            }

            if (!isset($configData[$defaultScopeKey])) {
                continue;
            }

            foreach ($configData[$defaultScopeKey] as $path => $value) {
                if (!array_key_exists($path, $configData[$scopeKey])) {
                    $configData[$scopeKey][$path] = $value;
                }
            }
        }

        return $this->convertToPlanData($configData, self::TYPE_CONFIG);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param EavSetup $eavSetup
     * @param string $entityIdField
     * @return array
     */
    public function getPlansFromProducts(
        ModuleDataSetupInterface $setup,
        EavSetup $eavSetup,
        string $entityIdField
    ): array {
        $productIds = [];
        $enabledTableName = $eavSetup->getAttributeTable(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::RECURRING_ENABLE
        );
        $enabledAttributeId = $eavSetup->getAttributeId(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::RECURRING_ENABLE
        );

        $attributeSelect = $setup->getConnection()
            ->select()
            ->from(['t' => $enabledTableName])
            ->where('`attribute_id` = ?', $enabledAttributeId)
            ->where(
                '`value` = "' . AvailableSubscription::CUSTOM_SETTING . '" OR ('
                . '`value` IN("' . AvailableSubscription::GLOBAL_SETTING . '", "' . AvailableSubscription::NO
                . '") AND `store_id` != 0)'
            );
        $rows = $setup->getConnection()->fetchAll($attributeSelect);

        $disabledProductAndScope = [];
        foreach ($rows as $row) {
            $productId = $row[$entityIdField];
            if (in_array($row['value'], [AvailableSubscription::GLOBAL_SETTING, AvailableSubscription::NO])) {
                $disabledProductAndScope[$productId . '_' . $row['store_id']] = true;
                continue;
            }
            $productIds[] = $productId;
        }

        if (!$productIds) {
            return [];
        }

        $attributes = [
            'am_billing_cycle' => 'billing_cycle',
            'am_billing_frequency' => 'billing_frequency',
            'am_billing_frequency_unit' => 'billing_frequency_unit',
            'am_enable_free_trials' => 'enable_free_trials',
            'am_number_trial_days' => 'number_of_trial_days',
            'am_enable_charge_fee' => 'charge_initial_fee',
            'am_initial_fee_type' => 'initial_fee_type',
            'am_initial_fee_amount' => 'initial_fee_amount',
            'am_enable_discounted_prices' => 'discounted_prices',
            'am_discount_type' => 'discount_type',
            'am_discount_amount' => 'discount_amount',
            'am_discount_percent' => 'discount_amount_percent',
            'am_enable_limit_discount_cycle' => 'enable_limit_discounted_cycles',
            'am_limit_discount_cycle' => 'number_discounted_cycles',
        ];

        $attributesDataToTable = [];
        $attributeIdsToKeys = [];
        foreach ($attributes as $key => $alias) {
            $attributeId = $eavSetup->getAttributeId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $key
            );
            $attributeTable = $eavSetup->getAttributeTable(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $key
            );

            $attributesDataToTable[$attributeTable][] = $attributeId;
            $attributeIdsToKeys[$attributeId] = $alias;
        }

        $productsData = [];
        foreach ($attributesDataToTable as $attributeTable => $attributeIds) {
            $select = $setup->getConnection()->select()
                ->from(
                    ['t' => $attributeTable]
                );
            $select->where('`attribute_id` IN(?)', $attributeIds);
            $select->where('`' . $entityIdField . '` IN(?)', $productIds);

            $rows = $setup->getConnection()->fetchAll($select);
            foreach ($rows as $row) {
                $scopedKey = $row[$entityIdField] . '_' . $row['store_id'];
                if (isset($disabledProductAndScope[$scopedKey])) {
                    continue;
                }
                $key = $attributeIdsToKeys[$row['attribute_id']];
                $productsData[$scopedKey][$key] = $row['value'];
            }
        }

        foreach ($productsData as $productScopeKey => $productScopedValues) {
            list($productId, $storeId) = explode('_', $productScopeKey);
            $defaultScopeKey = $productId . '_0';
            if ($storeId == 0 || !isset($productsData[$defaultScopeKey])) {
                continue;
            }

            foreach ($productsData[$defaultScopeKey] as $key => $value) {
                if (!array_key_exists($key, $productScopedValues)) {
                    $productsData[$productScopeKey][$key] = $value;
                }
            }
        }

        return $this->convertToPlanData($productsData, self::TYPE_PRODUCT);
    }

    /**
     * @param array $listAttributesWithValues
     * @param string $entityType
     * @return array
     */
    public function convertToPlanData(
        array $listAttributesWithValues,
        string $entityType
    ): array {
        $listPlansData = [];
        $defaultData = $this->getDefaultData();
        foreach ($listAttributesWithValues as $scopedKey => $scopeData) {
            empty($scopeData['billing_cycle']) && $scopeData['billing_cycle'] = 'customer_decide';
            $planData = $defaultData;
            if (!empty($scopeData['enable_free_trials'])
                && !empty(($scopeData['number_of_trial_days']))
                && $scopeData['enable_free_trials'] == Config\Source\EnableDisable::ENABLE
            ) {
                $planData[SubscriptionPlanInterface::ENABLE_TRIAL] = 1;
                $planData[SubscriptionPlanInterface::TRIAL_DAYS] = (int)$scopeData['number_of_trial_days'];
            }

            if (!empty($scopeData['charge_initial_fee'])
                && !empty($scopeData['initial_fee_amount'])
            ) {
                $planData[SubscriptionPlanInterface::ENABLE_INITIAL_FEE] = 1;
                $planData[SubscriptionPlanInterface::INITIAL_FEE_TYPE] =
                    $scopeData['initial_fee_type'] ?? AmountType::FIXED_AMOUNT;
                $planData[SubscriptionPlanInterface::INITIAL_FEE_AMOUNT] = (float)$scopeData['initial_fee_amount'];
            }

            if (!empty($scopeData['discounted_prices'])) {
                switch ($scopeData['discount_type'] ?? '') {
                    case AmountType::FIXED_AMOUNT:
                        if (!empty($scopeData['discount_amount'])) {
                            $planData[SubscriptionPlanInterface::ENABLE_DISCOUNT] = 1;
                            $planData[SubscriptionPlanInterface::DISCOUNT_TYPE] = $scopeData['discount_type'];
                            $planData[SubscriptionPlanInterface::DISCOUNT_AMOUNT] =
                                (float)$scopeData['discount_amount'];
                        }
                        break;
                    case AmountType::PERCENT_AMOUNT:
                        if (!empty($scopeData['discount_amount_percent'])) {
                            $planData[SubscriptionPlanInterface::ENABLE_DISCOUNT] = 1;
                            $planData[SubscriptionPlanInterface::DISCOUNT_TYPE] = $scopeData['discount_type'];
                            $planData[SubscriptionPlanInterface::DISCOUNT_AMOUNT] =
                                (float)(int)$scopeData['discount_amount_percent'];
                        }
                        break;
                }
            }

            if (!empty($planData[SubscriptionPlanInterface::ENABLE_DISCOUNT])
                && !empty($scopeData['enable_limit_discounted_cycles'])
                && !empty($scopeData['number_discounted_cycles'])
            ) {
                $planData[SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT] = 1;
                $planData[SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES] =
                    (int)$scopeData['number_discounted_cycles'];
            }

            list($entity, $scopeId) = explode('_', $scopedKey);

            switch ($scopeData['billing_cycle']) {
                case Config\Source\BillingCycle::ONCE_DAY:
                    $planData[SubscriptionPlanInterface::FREQUENCY] = 1;
                    $planData[SubscriptionPlanInterface::FREQUENCY_UNIT] = BillingFrequencyUnit::DAY;
                    break;
                case Config\Source\BillingCycle::ONCE_WEEK:
                    $planData[SubscriptionPlanInterface::FREQUENCY] = 1;
                    $planData[SubscriptionPlanInterface::FREQUENCY_UNIT] = BillingFrequencyUnit::WEEK;
                    break;
                case Config\Source\BillingCycle::ONCE_MONTH:
                    $planData[SubscriptionPlanInterface::FREQUENCY] = 1;
                    $planData[SubscriptionPlanInterface::FREQUENCY_UNIT] = BillingFrequencyUnit::MONTH;
                    break;
                case Config\Source\BillingCycle::ONCE_YEAR:
                    $planData[SubscriptionPlanInterface::FREQUENCY] = 1;
                    $planData[SubscriptionPlanInterface::FREQUENCY_UNIT] = BillingFrequencyUnit::YEAR;
                    break;
                case Config\Source\BillingCycle::CUSTOM:
                    $planData[SubscriptionPlanInterface::FREQUENCY] = (int)($scopeData['billing_frequency'] ?? 1);
                    $planData[SubscriptionPlanInterface::FREQUENCY_UNIT] = $scopeData['billing_frequency_unit']
                        ?? BillingFrequencyUnit::DAY;
                    break;
                case Config\Source\BillingCycle::CUSTOMER_DECIDE:
                    $listDefaultPlansData = $this->getDefaultPlansData();
                    foreach ($listDefaultPlansData as $defaultPlanData) {
                        $item = $planData;
                        foreach ($defaultPlanData as $key => $value) {
                            !isset($item[$key]) && $item[$key] = $value;
                        }
                        $item[SubscriptionPlanInterface::NAME] = $this->getLabel($item);
                        $listPlansData[] = [
                            'plan' => $item,
                            'type' => $entityType,
                            'entity' => $entity,
                            'store_id' => $scopeId
                        ];
                    }
                    break;
            }

            if ($scopeData['billing_cycle'] !== Config\Source\BillingCycle::CUSTOMER_DECIDE) {
                $planData[SubscriptionPlanInterface::NAME] = $this->getLabel($planData);
                $listPlansData[] = [
                    'plan' => $planData,
                    'type' => $entityType,
                    'entity' => $entity,
                    'store_id' => $scopeId
                ];
            }
        }

        return $listPlansData;
    }

    /**
     * @return array
     */
    private function getDefaultData()
    {
        return [
            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
        ];
    }

    /**
     * @param array $planData
     * @return string
     */
    private function getLabel(array $planData)
    {
        $label = [];
        if ($planData[SubscriptionPlanInterface::FREQUENCY] == 1) {
            $label[] = $this->getLabelForOnceFrequency($planData[SubscriptionPlanInterface::FREQUENCY_UNIT]);
        } else {
            $label[] = (string)$this->billingFrequencyLabelMapper->getLabel(
                $planData[SubscriptionPlanInterface::FREQUENCY],
                $planData[SubscriptionPlanInterface::FREQUENCY_UNIT]
            );
        }

        $label[] = 'with';

        $isTrial = false;
        if (!empty($planData[SubscriptionPlanInterface::ENABLE_TRIAL])) {
            $isTrial = true;
            $label[] = 'trial';
        }

        $isFee = false;
        if (!empty($planData[SubscriptionPlanInterface::ENABLE_INITIAL_FEE])) {
            $isFee = true;
            if ($isTrial) {
                $label[] = 'and';
            }
            $label[] = $planData[SubscriptionPlanInterface::INITIAL_FEE_TYPE] === AmountType::FIXED_AMOUNT
                ? 'fixed'
                : 'percentage';
            $label[] = 'initial';
            $label[] = 'fee';
        }

        if (!empty($planData[SubscriptionPlanInterface::ENABLE_DISCOUNT])) {
            if ($isFee || $isTrial) {
                $label[] = 'and';
            }

            if (!empty($planData[SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT])) {
                $label[] = 'limited';
            }
            $label[] = $planData[SubscriptionPlanInterface::DISCOUNT_TYPE] === AmountType::FIXED_AMOUNT
                ? 'fixed'
                : 'percentage';
            $label[] = 'discount';
        }

        if (count($label) === 2) {
            array_pop($label);
        }

        return implode(' ', $label);
    }

    /**
     * @param string $frequencyUnit
     * @return string
     */
    private function getLabelForOnceFrequency(string $frequencyUnit): string
    {
        $label = '';
        switch ($frequencyUnit) {
            case BillingFrequencyUnit::DAY:
                $label = self::LABEL_DAILY;
                break;
            case BillingFrequencyUnit::WEEK:
                $label = self::LABEL_WEEKLY;
                break;
            case BillingFrequencyUnit::MONTH:
                $label = self::LABEL_MONTHLY;
                break;
            case BillingFrequencyUnit::YEAR:
                $label = self::LABEL_ANNUAL;
                break;
        }

        return $label;
    }
}
