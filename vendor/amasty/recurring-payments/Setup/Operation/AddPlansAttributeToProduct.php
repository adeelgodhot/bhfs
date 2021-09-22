<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Api\Data\ProductRecurringGroupInterface;
use Amasty\RecurringPayments\Model\Config\Source\SubscriptionPlan;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddPlansAttributeToProduct
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

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::PLANS,
            [
                'label' => 'Subscription Plans Available to Customers',
                'type' => 'text',
                'source' => SubscriptionPlan::class,
                'sort_order' => 30,
                'input' => 'multiselect',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                'visible' => true,
                'required' => false,
                'note' => 'Select one or multiple plans your customers would be able to choose from when subscribing' .
                    'to the product. If you need to add more plans or modify existing ones, please visit Sales >' .
                    ' Amasty Subscriptions > Subscription Plans.'
            ]
        );
    }
}
