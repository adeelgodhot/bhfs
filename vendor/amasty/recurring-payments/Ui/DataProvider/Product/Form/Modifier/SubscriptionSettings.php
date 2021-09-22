<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Ui\DataProvider\Product\Form\Modifier;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Magento\Backend\Model\Url;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Framework\Stdlib\ArrayManager;

class SubscriptionSettings extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Url
     */
    private $url;

    public function __construct(
        ArrayManager $arrayManager,
        Url $url
    ) {
        $this->arrayManager = $arrayManager;
        $this->url = $url;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->addDependsToFields($meta);
        $meta = $this->addDefaultValue($meta);
        $meta = $this->removeFields($meta);

        return $meta;
    }

    /**
     * @param array $meta
     * @return array
     */
    private function addDependsToFields(array $meta)
    {
        $globalSetting = '${$.ns}.${$.ns}.subscription-settings.'
            . self::CONTAINER_PREFIX
            . ProductRecurringAttributesInterface::RECURRING_ENABLE
            . '.' . ProductRecurringAttributesInterface::RECURRING_ENABLE;

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                ProductRecurringAttributesInterface::RECURRING_ENABLE,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'component' => 'Amasty_RecurringPayments/js/form/element/available-subscription',
                'template' => 'Amasty_RecurringPayments/ui/form/field',
                'notice' => __(
                    "Go to <a href='%1' target='_blank'>Stores > Configuration</a> to check other subscription "
                    . "settings.",
                    $this->url->getUrl('adminhtml/system_config/edit/section/amasty_recurring_payments')
                ),
                'listens' => [
                    $globalSetting . ':value' => 'onChange'
                ],
                'imports' => [
                    'allowed' => AvailableSubscription::CUSTOM_SETTING,
                    'fields' => '${$.ns}.${$.ns}.subscription-settings',
                    '__disableTmpl' => ['fields' => false],
                ]
            ]
        );

        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                ProductRecurringAttributesInterface::PLANS,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'template' => 'Amasty_RecurringPayments/ui/form/field',
                'notice' => __(
                    "Select one or multiple plans your customers would be able to choose from when subscribing"
                    . " to the product. If you need to add more plans or modify existing ones, please visit"
                    . " <a href='%1' target='_blank'>Sales > Amasty Subscriptions > Subscription Plans</a>.",
                    $this->url->getUrl('amasty_recurring/plan/index')
                ),
                'listens' => [
                    $globalSetting . ':value' => 'onChange'
                ],
                'imports' => [
                    'allowed' => AvailableSubscription::CUSTOM_SETTING,
                    'fields' => '${$.ns}.${$.ns}.subscription-settings',
                    '__disableTmpl' => ['fields' => false],
                ]
            ]
        );

        return $meta;
    }

    /**
     * @param array $meta
     * @return array
     */
    private function addDefaultValue(array $meta)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                ProductRecurringAttributesInterface::SUBSCRIPTION_ONLY,
                $meta,
                null,
                'children'
            ) . static::META_CONFIG_PATH,
            $meta,
            [
                'value' => Boolean::VALUE_NO
            ]
        );

        return $meta;
    }

    /**
     * @TODO: remove this after deleting attributes
     * @param array $meta
     * @return array
     */
    private function removeFields(array $meta)
    {
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
            $containerName = self::CONTAINER_PREFIX . $attributeCode;
            unset($meta['subscription-settings']['children'][$containerName]);
        }

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
