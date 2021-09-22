<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Test\Integration\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Config\Source\PlanStatus;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan;
use Amasty\RecurringPayments\Setup\Operation\CreatePlansAndAssign;
use Amasty\RecurringPayments\Setup\Operation\ToPlanMigration\PlansFormer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class CreatePlansAndAssignTest
 *
 * @see CreatePlansAndAssign
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CreatePlansAndAssignTest extends TestCase
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $configTableName;

    /**
     * @var string
     */
    private $subscriptionPlanTableName;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $planRows = [];
    private $oldConfigValues = [];
    private $configFixtures = [];
    private $resultConfigs = [];


    public function setUp(): void
    {
        /** @var ModuleDataSetupInterface $moduleDataSetup */
        $this->moduleDataSetup = Bootstrap::getObjectManager()->get(ModuleDataSetupInterface::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);

        $this->connection = $this->moduleDataSetup->getConnection();
        $this->configTableName = $this->moduleDataSetup->getTable('core_config_data');
        $this->subscriptionPlanTableName = $this->moduleDataSetup->getTable(SubscriptionPlan::TABLE_NAME);

        $select = $this->connection->select()
            ->from(['t' => $this->subscriptionPlanTableName]);

        $this->planRows = $this->connection->fetchAll($select);
        $this->connection->delete($this->subscriptionPlanTableName);
    }

    public function tearDown(): void
    {
        $this->connection->delete($this->subscriptionPlanTableName);
        if ($this->planRows) {
            $this->connection->insertMultiple(
                $this->subscriptionPlanTableName,
                $this->planRows
            );
        }

        // drop new values and then save the old values
        $this->clearValues($this->configFixtures);
        $this->clearValues($this->resultConfigs);
        $this->saveValues($this->oldConfigValues);
    }

    /**
     * @covers       CreatePlansAndAssign::execute
     * @dataProvider dataProvider
     * @param array $configFixtures
     * @param array $listProductsWithAttributes
     * @param array $resultPlans
     * @param array $resultConfigs
     */
    public function testExecute($configFixtures, $listProductsWithAttributes, $resultPlans, $resultConfigs)
    {
        // backup old values, then drop them and save new values
        $this->oldConfigValues = $this->loadValues($configFixtures);
        $this->configFixtures = $configFixtures;
        $this->resultConfigs = $resultConfigs;
        $this->clearValues($configFixtures);
        $this->clearValues($resultConfigs);
        $this->saveValues($configFixtures);

        $products = [];
        foreach ($listProductsWithAttributes as $productAttributes) {
            $products[] = $this->createProductWithAttributeValues($productAttributes['attributes']);
        }

        /** @var CreatePlansAndAssign $createPlansAndAssign */
        $createPlansAndAssign = Bootstrap::getObjectManager()->get(
            CreatePlansAndAssign::class
        );

        $createPlansAndAssign->execute($this->moduleDataSetup);

        foreach ($resultConfigs as $resultRow) {
            $select = $this->connection
                ->select()
                ->from($this->configTableName, [new \Zend_Db_Expr('COUNT(*)')])
                ->where('path = ?', $resultRow['path'])
                ->where('scope = ?', $resultRow['scope'])
                ->where('scope_id = ?', $resultRow['scope_id']);
            $valueFromDb = $this->connection->fetchOne($select);
            $this->assertEquals(1, $valueFromDb);
        }

        foreach ($resultPlans as $resultPlan) {
            $select = $this->connection
                ->select()
                ->from($this->subscriptionPlanTableName, [new \Zend_Db_Expr('COUNT(*)')]);
            foreach ($resultPlan as $planFieldName => $planFieldValue) {
                $select->where($planFieldName . ' = ?', $planFieldValue);
            }
            $valueFromDb = $this->connection->fetchOne($select);
            $this->assertEquals(1, $valueFromDb);
        }

        $select = $this->connection
            ->select()
            ->from($this->subscriptionPlanTableName, [new \Zend_Db_Expr('COUNT(*)')]);
        $valueFromDb = $this->connection->fetchOne($select);
        $this->assertEquals(count($resultPlans), $valueFromDb);

        foreach ($products as $product) {
            $this->productRepository->delete($product);
        }
    }

    /**
     * @param array $configRows
     * @return array
     */
    private function loadValues($configRows)
    {
        $values = $configRows;
        foreach ($values as $valueIndex => $configRow) {
            $select = $this->connection
                ->select()
                ->from($this->configTableName, ['value'])
                ->where('path = ?', $configRow['path'])
                ->where('scope = ?', $configRow['scope'])
                ->where('scope_id = ?', $configRow['scope_id']);
            $value = $this->connection->fetchOne($select);
            if ($value === false) {
                unset($values[$valueIndex]);
            } else {
                $values[$valueIndex]['value'] = $value;
            }
        }

        return $values;
    }

    /**
     * @param array $configRows
     */
    private function clearValues($configRows)
    {
        foreach ($configRows as $configRow) {
            $this->connection->delete(
                $this->configTableName,
                [
                    'path = ?' => $configRow['path'],
                    'scope = ?' => $configRow['scope'],
                    'scope_id = ?' => $configRow['scope_id']
                ]
            );
        }
    }

    /**
     * @param array $configRows
     */
    private function saveValues($configRows)
    {
        if (empty($configRows)) {
            return;
        }
        $this->connection->insertMultiple(
            $this->configTableName,
            $configRows
        );
    }

    /**
     * @param array $attributesAndValues
     */
    private function createProductWithAttributeValues(array $attributesAndValues)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
        $skuAndUrlKey = uniqid('sku_', true);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->setAttributeSetId(4)
            ->setWebsiteIds([1])
            ->setName('Simple Product ' . $skuAndUrlKey)
            ->setSku($skuAndUrlKey)
            ->setPrice(10)
            ->setDescription('Description with <b>html tag</b>')
            ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setCategoryIds([2])
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
            ->setUrlKey($skuAndUrlKey);

        $product->addData($attributesAndValues);

        $this->productRepository->save($product);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        /** @var StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()->create(StoreManagerInterface::class);
        $store = $storeManager->getDefaultStoreView();
        $storeId = $store->getId();
        $websiteId = $store->getWebsiteId();

        $pathPrefix = Config::PATH_PREFIX . Config::GLOBAL_BLOCK;

        return [
            'emptyAllData' => [
                [],
                [],
                [
                    [
                        SubscriptionPlanInterface::NAME => 'Daily',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Weekly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Monthly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Annual',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                ],
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                ]

            ],
            'allConfigCustomFrequency' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOM,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOM,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOM,
                    ],
                ],
                [],
                [
                    [
                        SubscriptionPlanInterface::NAME => 'Daily',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Weekly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Monthly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Annual',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                ],
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                ]
            ],
            'allConfigCustomerDecidedFrequency' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOMER_DECIDE,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOMER_DECIDE,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOMER_DECIDE,
                    ],
                ],
                [],
                [
                    [
                        SubscriptionPlanInterface::NAME => 'Daily',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Weekly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Monthly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Annual',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                ],
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                ]
            ],
            'hodgePodge' => [
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOMER_DECIDE,
                    ],
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . 'enable_free_trials',
                        'value' => Config\Source\EnableDisable::ENABLE,
                    ],
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . 'number_of_trial_days',
                        'value' => 2,
                    ],
                    // website has different settings
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'billing_cycle',
                        'value' => Config\Source\BillingCycle::CUSTOM,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'billing_frequency',
                        'value' => 3,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'billing_frequency_unit',
                        'value' => BillingFrequencyUnit::WEEK,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'enable_free_trials',
                        'value' => Config\Source\EnableDisable::ENABLE,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'number_of_trial_days',
                        'value' => 3,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'charge_initial_fee',
                        'value' => 1,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'initial_fee_type',
                        'value' => Config\Source\AmountType::FIXED_AMOUNT,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'initial_fee_amount',
                        'value' => 40,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'discounted_prices',
                        'value' => 1,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'discount_type',
                        'value' => Config\Source\AmountType::PERCENT_AMOUNT,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'discount_amount_percent',
                        'value' => 30,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'enable_limit_discounted_cycles',
                        'value' => 1,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . 'number_discounted_cycles',
                        'value' => 8,
                    ],

                    // store inherit website, but have one different setting
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . 'number_discounted_cycles',
                        'value' => 10,
                    ],
                ],
                [
                ],
                [
                    [
                        SubscriptionPlanInterface::NAME => 'Daily',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Weekly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Monthly',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Annual',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Daily with trial',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 2,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Weekly with trial',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 2,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Monthly with trial',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 2,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Annual with trial',
                        SubscriptionPlanInterface::FREQUENCY => 1,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 2,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Every 3 weeks with trial and fixed initial fee and limited percentage discount',
                        SubscriptionPlanInterface::FREQUENCY => 3,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 3,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 1,
                        SubscriptionPlanInterface::INITIAL_FEE_TYPE => Config\Source\AmountType::FIXED_AMOUNT,
                        SubscriptionPlanInterface::INITIAL_FEE_AMOUNT => 40,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 1,
                        SubscriptionPlanInterface::DISCOUNT_TYPE => Config\Source\AmountType::PERCENT_AMOUNT,
                        SubscriptionPlanInterface::DISCOUNT_AMOUNT => 30,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT => 1,
                        SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES => 8,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                    [
                        SubscriptionPlanInterface::NAME => 'Every 3 weeks with trial and fixed initial fee and limited percentage discount',
                        SubscriptionPlanInterface::FREQUENCY => 3,
                        SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                        SubscriptionPlanInterface::TRIAL_DAYS => 3,
                        SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 1,
                        SubscriptionPlanInterface::INITIAL_FEE_TYPE => Config\Source\AmountType::FIXED_AMOUNT,
                        SubscriptionPlanInterface::INITIAL_FEE_AMOUNT => 40,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT => 1,
                        SubscriptionPlanInterface::DISCOUNT_TYPE => Config\Source\AmountType::PERCENT_AMOUNT,
                        SubscriptionPlanInterface::DISCOUNT_AMOUNT => 30,
                        SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT => 1,
                        SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES => 10,
                        SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                    ],
                ],
                [
                    [
                        'scope' => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        'scope_id' => 0,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_WEBSITES,
                        'scope_id' => $websiteId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                    [
                        'scope' => ScopeInterface::SCOPE_STORES,
                        'scope_id' => $storeId,
                        'path' => $pathPrefix . Config::SUBSCRIPTION_PLANS,
                    ],
                ]
            ]
        ];
    }
}
