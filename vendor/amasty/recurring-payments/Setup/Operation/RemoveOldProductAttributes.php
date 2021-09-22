<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class RemoveOldProductAttributes
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $attributesToDelete = [
            ProductRecurringAttributesInterface::BILLING_CYCLE,
            ProductRecurringAttributesInterface::BILLING_FREQUENCY,
            ProductRecurringAttributesInterface::BILLING_FREQUENCY_UNIT,
            ProductRecurringAttributesInterface::ENABLE_FREE_TRIALS,
            ProductRecurringAttributesInterface::NUMBER_TRIAL_DAYS,
            ProductRecurringAttributesInterface::ENABLE_CHARGE_FEE,
            ProductRecurringAttributesInterface::INITIAL_FEE_TYPE,
            ProductRecurringAttributesInterface::INITIAL_FEE_AMOUNT,
            ProductRecurringAttributesInterface::ENABLE_DISCOUNTED_PRICES,
            ProductRecurringAttributesInterface::DISCOUNT_TYPE,
            ProductRecurringAttributesInterface::DISCOUNT_AMOUNT,
            ProductRecurringAttributesInterface::DISCOUNT_PERCENT,
            ProductRecurringAttributesInterface::ENABLE_LIMIT_DISCOUNT_CYCLE,
            ProductRecurringAttributesInterface::LIMIT_DISCOUNT_CYCLE,
        ];

        foreach ($attributesToDelete as $attributeCode) {
            $eavSetup->removeAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
        }
    }
}
