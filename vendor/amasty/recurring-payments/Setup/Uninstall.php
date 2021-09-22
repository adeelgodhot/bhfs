<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Api\Data\ProductRecurringGroupInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Fee;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction;
use Amasty\RecurringPayments\Model\ResourceModel\Discount;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
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
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codingStandardsIgnoreStart
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();
        $this->removeAttributes($eavSetup);
        $this->removeGroups($eavSetup);
        $this->removeTables($setup);
    }

    /**
     * @param EavSetup $eavSetup
     */
    private function removeAttributes(EavSetup $eavSetup)
    {
        $reflectionClass = new \ReflectionClass(ProductRecurringAttributesInterface::class);
        $attributes = $reflectionClass->getConstants();

        foreach ($attributes as $attribute) {
            $eavSetup->removeAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attribute
            );
        }
    }

    /**
     * @param EavSetup $eavSetup
     */
    private function removeGroups(EavSetup $eavSetup)
    {
        $reflectionClass = new \ReflectionClass(ProductRecurringGroupInterface::class);
        $groups = $reflectionClass->getConstants();

        foreach ($groups as $group) {
            $eavSetup->removeAttributeGroup(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default',
                $group
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function removeTables(SchemaSetupInterface $setup)
    {
        $setup->startSetup()->getConnection()->delete(Fee::TABLE_NAME);
        $setup->startSetup()->getConnection()->delete(Transaction::TABLE_NAME);
        $setup->startSetup()->getConnection()->delete(Subscription::TABLE_NAME);
        $setup->startSetup()->getConnection()->delete(Discount::TABLE_NAME);
        $setup->startSetup()->getConnection()->delete(Schedule::TABLE_NAME);
    }
}
