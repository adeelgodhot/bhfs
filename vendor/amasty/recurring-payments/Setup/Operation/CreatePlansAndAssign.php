<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan;
use Amasty\RecurringPayments\Setup\Operation\ToPlanMigration\PlansFormer;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CreatePlansAndAssign
{
    /**
     * @var PlansFormer
     */
    private $plansFormer;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        PlansFormer $plansFormer,
        EavSetupFactory $eavSetupFactory,
        SerializerInterface $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->plansFormer = $plansFormer;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $existingKeys = [];
        $existingKeys = $this->createDefaultPlans($setup, $existingKeys);
        $existingKeys = $this->createPlansFromConfig($setup, $existingKeys);
        $this->createPlansFromProduct($setup, $existingKeys);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $existingKeys
     * @return array
     */
    private function createDefaultPlans(ModuleDataSetupInterface $setup, array $existingKeys)
    {
        foreach ($this->plansFormer->getDefaultPlansData() as $planData) {
            $existingKeys[$this->getPlanKey($planData)] = $this->createPlan($setup, $planData);
        }

        return $existingKeys;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $existingKeys
     * @return array
     */
    private function createPlansFromConfig(ModuleDataSetupInterface $setup, array $existingKeys)
    {
        $scopedData = $this->createPlansIfNeedAndGetScopedData(
            $setup,
            $this->plansFormer->getPlansFromConfig($setup),
            $existingKeys
        );

        $configRows = [];
        foreach ($scopedData as $scopedKey => $values) {
            list($scope, $scopeId) = explode('_', $scopedKey);
            $configRows[] = [
                'path' => Config::PATH_PREFIX . Config::GLOBAL_BLOCK . Config::SUBSCRIPTION_PLANS,
                'scope' => $scope,
                'scope_id' => $scopeId,
                'value' => implode(',', $values),
            ];
        }

        if (empty($configRows)) {
            $configRows[] = [
                'path' => Config::PATH_PREFIX . Config::GLOBAL_BLOCK . Config::SUBSCRIPTION_PLANS,
                'scope' => 'default',
                'scope_id' => 0,
                'value' => implode(',', $existingKeys),
            ];
        }

        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->insertMultiple($configTable, $configRows);

        return $existingKeys;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $existingKeys
     * @return array
     */
    private function createPlansFromProduct(ModuleDataSetupInterface $setup, array $existingKeys)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavAttributeValueTable = $eavSetup->getAttributeTable(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::PLANS
        );
        $attributeId = $eavSetup->getAttributeId(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::PLANS
        );

        $columns = $setup->getConnection()->describeTable($eavAttributeValueTable);
        $entityIdField = 'entity_id';
        foreach ($columns as $column) {
            if ($column['COLUMN_NAME'] == 'row_id') {
                $entityIdField = 'row_id';
                continue;
            }
        }

        $scopedData = $this->createPlansIfNeedAndGetScopedData(
            $setup,
            $this->plansFormer->getPlansFromProducts($setup, $eavSetup, $entityIdField),
            $existingKeys
        );

        if ($scopedData) {
            $tableRows = [];
            foreach ($scopedData as $scopedKey => $values) {
                list($productId, $storeId) = explode('_', $scopedKey);
                $tableRows[] = [
                    'attribute_id' => $attributeId,
                    $entityIdField => $productId,
                    'store_id' => $storeId,
                    'value' => implode(',', $values),
                ];
            }

            $setup->getConnection()->insertMultiple($eavAttributeValueTable, $tableRows);
        }

        return $existingKeys;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $planData
     * @return int
     */
    private function createPlan(ModuleDataSetupInterface $setup, array $planData): int
    {
        $table = $setup->getTable(SubscriptionPlan::TABLE_NAME);
        $setup->getConnection()->insert($table, $planData);
        return (int)$setup->getConnection()->lastInsertId();
    }

    /**
     * @param array $planData
     * @return string
     */
    private function getPlanKey(array $planData): string
    {
        ksort($planData);
        $key = $this->serializer->serialize($planData);

        return  $this->encryptor->hash($key);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $items
     * @param array $existingKeys
     * @return array
     */
    private function createPlansIfNeedAndGetScopedData(
        ModuleDataSetupInterface $setup,
        array $items,
        array &$existingKeys
    ): array {
        $scopedData = [];
        foreach ($items as $planAndScopeData) {
            $planKey = $this->getPlanKey($planAndScopeData['plan']);
            if (isset($existingKeys[$planKey])) {
                $planId = $existingKeys[$planKey];
            } else {
                $planId = $this->createPlan($setup, $planAndScopeData['plan']);
                $existingKeys[$planKey] = $planId;
            }

            $scopedData[$planAndScopeData['entity'] . '_' . $planAndScopeData['store_id']][] = $planId;
        }

        return $scopedData;
    }
}
