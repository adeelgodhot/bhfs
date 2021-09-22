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
use Amasty\RecurringPayments\Model\Config\Source\BillingCycle;
use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Config\Source\EnableDisable;
use Amasty\RecurringPayments\Model\Config\Source\AmountType;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Magento\Backend\Model\Url;
use Magento\Catalog\Model\Product\Attribute\Backend\Boolean as BackendBoolean;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\App\State;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Url $url,
        State $appState
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->url = $url;
        $this->appState = $appState;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->appState->emulateAreaCode(
            \Magento\Framework\App\Area::AREA_ADMINHTML,
            [$this, 'installCallback'],
            [$setup, $context]
        );
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function installCallback(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttributeGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            min($eavSetup->getAllAttributeSetIds('catalog_product')),
            ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
            1000
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::RECURRING_ENABLE,
            [
                'label' => 'Available by Subscription',
                'type' => 'varchar',
                'default_value' => AvailableSubscription::NO,
                'source' => AvailableSubscription::class,
                'sort_order' => 10,
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::SUBSCRIPTION_ONLY,
            [
                'label' => 'Subscription only',
                'backend' => BackendBoolean::class,
                'input' => 'select',
                'source' => Boolean::class,
                'default_value' => Boolean::VALUE_NO,
                'sort_order' => 20,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Select 'No' to make this product available as either regular purchase or subscription. "
                    . "Select 'Yes' to make this product subscription only."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::BILLING_CYCLE,
            [
                'label' => 'Billing Cycle',
                'input' => 'select',
                'source' => BillingCycle::class,
                'default_value' => BillingCycle::CUSTOM,
                'sort_order' => 30,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Keep in mind that the moment of purchase (i.e., customer placed an order containing "
                    . "subscription products) is the starting point for all billing cycles."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::BILLING_FREQUENCY,
            [
                'label' => 'Billing Frequency',
                'type' => 'int',
                'input' => 'text',
                'sort_order' => 40,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive integers only. Required for billing your customers every N "
                    . "days/weeks/months/years depending on the selected frequency unit."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::BILLING_FREQUENCY_UNIT,
            [
                'label' => 'Billing Frequency Unit',
                'input' => 'select',
                'source' => BillingFrequencyUnit::class,
                'default_value' => BillingFrequencyUnit::DAY,
                'sort_order' => 50,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "This is used in combination with billing frequency to define the interval of time from "
                    . "the end of one billing, or invoice, statement date to the next billing statement date."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::ENABLE_FREE_TRIALS,
            [
                'label' => 'Enable Free Trials',
                'input' => 'select',
                'source' => EnableDisable::class,
                'sort_order' => 60,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Enable this option if you want your customers to test the product for free prior charging "
                    . "them a normal subscription price."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::NUMBER_TRIAL_DAYS,
            [
                'label' => 'Number of Trial Days',
                'input' => 'text',
                'sort_order' => 70,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive integers only. Your customer will not be charged a subscription fee for using the "
                    . "product in a matter of first N days from the moment of order placement."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::ENABLE_CHARGE_FEE,
            [
                'label' => 'Charge Initial Fee',
                'input' => 'select',
                'source' => Boolean::class,
                'sort_order' => 80,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Choose whether you want to charge your customers initial subscription fee or not. This "
                    . "will be charged only once at the moment of first purchase not affecting future billing cycles."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::INITIAL_FEE_TYPE,
            [
                'label' => 'Initial Fee Type',
                'input' => 'select',
                'source' => AmountType::class,
                'sort_order' => 90,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Fee can be either a fixed amount in the base store currency or a percent of the regular "
                    . "price of the product."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::INITIAL_FEE_AMOUNT,
            [
                'label' => 'Initial Fee Amount',
                'input' => 'text',
                'sort_order' => 100,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive floating point numbers only. If there are multiple different subscription "
                    . "products in a single order, initial fee will be calculated as a sum of individual fees."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::ENABLE_DISCOUNTED_PRICES,
            [
                'label' => 'Offer Discounted Prices to Subscribers',
                'input' => 'select',
                'source' => Boolean::class,
                'sort_order' => 110,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Customers would be able to save money when subscribing to this product instead of making "
                    . "a regular purchase."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::DISCOUNT_TYPE,
            [
                'label' => 'Discount Type',
                'input' => 'select',
                'source' => AmountType::class,
                'sort_order' => 120,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Discount can be either a fixed amount in the base store currency or a percent of the "
                    . "regular price of the product."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::DISCOUNT_AMOUNT,
            [
                'label' => 'Discount Amount',
                'input' => 'text',
                'sort_order' => 130,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive floating point numbers only. This amount will be deducted from the regular "
                    . "price of this product."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::DISCOUNT_PERCENT,
            [
                'label' => 'Discount Amount',
                'input' => 'text',
                'sort_order' => 131,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive floating point numbers only. This amount will be deducted from the regular "
                    . "price of this product."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::ENABLE_LIMIT_DISCOUNT_CYCLE,
            [
                'label' => 'Limit the Number of Discounted Cycles',
                'input' => 'select',
                'source' => Boolean::class,
                'sort_order' => 140,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "You can choose whether to always apply discount to the amount your customer pays for the "
                    . "subscription or limit the number of billing cycles with discounted pricing."
            ]
        );

        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            ProductRecurringAttributesInterface::LIMIT_DISCOUNT_CYCLE,
            [
                'label' => 'Number of Discounted Cycles',
                'input' => 'text',
                'sort_order' => 150,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'group' => ProductRecurringGroupInterface::SUBSCRIPTION_SETTINGS_GROUP,
                'visible' => true,
                'required' => false,
                'note' => "Positive integers only. Your customer will have discount on payments for the subscription "
                    . "only in a matter of first N billing cycles."
            ]
        );
    }
}
