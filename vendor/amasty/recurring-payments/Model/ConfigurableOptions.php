<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Amasty\RecurringPayments\Model\Subscription\Mapper\StartEndDateMapper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Quote\Api\Data\CartItemInterface;

class ConfigurableOptions
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var StartEndDateMapper
     */
    private $startEndDateMapper;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Product $product,
        Amount $amount,
        StartEndDateMapper $startEndDateMapper,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        Date $date,
        ItemDataRetriever $itemDataRetriever
    ) {
        $this->productRepository = $productRepository;
        $this->product = $product;
        $this->amount = $amount;
        $this->startEndDateMapper = $startEndDateMapper;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
        $this->date = $date;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param CartItemInterface $item
     *
     * @return array
     */
    public function getCustomOptions(CartItemInterface $item): array
    {
        $plan = $this->itemDataRetriever->getPlan($item, false);
        $customOptions = [];
        /** @var MagentoProduct $product */
        $product = $this->productRepository->getById($item->getProduct()->getId());

        // @TODO: refactor this
        $this->setBillingCycle($plan, $customOptions);
        $this->setFreeTrial($plan, $customOptions);
        $this->setInitialFee($product, $plan, $customOptions, $item);
        $this->setDiscountCycle($plan, $customOptions);
        $this->setFreeShipping($customOptions);
        $this->setStartEndDate($item, $plan, $customOptions);

        return $customOptions;
    }

    /**
     * @param SubscriptionPlanInterface $plan
     * @param array $customOptions
     *
     * @return void
     */
    private function setBillingCycle(
        SubscriptionPlanInterface $plan,
        array &$customOptions
    ) {
        $value = $this->billingFrequencyLabelMapper->getLabel(
            $plan->getFrequency(),
            $plan->getFrequencyUnit()
        );
        $customOptions[] = [
            'label' => (string)__('Billing Cycle & Delivery'),
            'value' => (string)$value,
            'print_value' => (string)$value,
            'option_type' => 'field',
            'option_view' => false,
        ];
    }

    /**
     * @param SubscriptionPlanInterface $plan
     * @param array $customOptions
     *
     * @return void
     */
    private function setFreeTrial(SubscriptionPlanInterface $plan, array &$customOptions)
    {
        $isEnableTrial = $plan->getEnableTrial() && $plan->getTrialDays();
        $value = $isEnableTrial ? __('Yes') : __('No');
        $customOptions[] = [
            'label' => (string)__('Free Trials'),
            'value' => (string)$value,
            'print_value' => (string)$value,
            'option_type' => 'field',
            'option_view' => false,
        ];

        if ($isEnableTrial) {
            $customOptions[] = [
                'label' => (string)__('Number of Trial Days'),
                'value' => $plan->getTrialDays(),
                'print_value' => $plan->getTrialDays(),
                'option_type' => 'field',
                'option_view' => false,
            ];
        }
    }

    /**
     * @param MagentoProduct $product
     * @param SubscriptionPlanInterface $plan
     * @param array $customOptions
     * @param CartItemInterface|null $item
     *
     * @return void
     */
    private function setInitialFee(
        MagentoProduct $product,
        SubscriptionPlanInterface $plan,
        array &$customOptions,
        CartItemInterface $item = null
    ) {
        $isEnabledFee = $plan->getEnableInitialFee() && $plan->getInitialFeeAmount();

        if ($isEnabledFee) {
            $amount = $this->amount->getAmount(
                $product,
                (float)$plan->getInitialFeeAmount(),
                $plan->getInitialFeeType(),
                $item
            );
            $customOptions[] = [
                'label' => (string)__('Initial Fee (excl. tax)'),
                'value' => $this->amount->convertAndFormat($amount),
                'print_value' => $this->amount->convertAndFormat($amount),
                'option_type' => 'field',
                'option_view' => false,
            ];
        }
    }

    /**
     * @param SubscriptionPlanInterface $plan
     * @param array $customOptions
     *
     * @return void
     */
    private function setDiscountCycle(SubscriptionPlanInterface $plan, array &$customOptions)
    {
        $isEnabled = $plan->getEnableDiscount()
            && $plan->getDiscountAmount()
            && $plan->getEnableDiscountLimit()
            && $plan->getNumberOfDiscountCycles();

        if (!$isEnabled) {
            return;
        }
        $customOptions[] = [
            'label' => (string)__('Discounted Cycles'),
            'value' => $plan->getNumberOfDiscountCycles(),
            'print_value' => $plan->getNumberOfDiscountCycles(),
            'option_type' => 'field',
            'option_view' => false,
        ];
    }

    /**
     * @param array $customOptions
     *
     * @return void
     */
    private function setFreeShipping(array &$customOptions)
    {
        $value = $this->product->isFreeShipping() ? __('Yes') : __('No');
        $customOptions[] = [
            'label' => (string)__('Free Shipping on This Product'),
            'value' => (string)$value,
            'print_value' => (string)$value,
            'option_type' => 'field',
            'option_view' => false,
        ];
    }

    /**
     * @param CartItemInterface $item
     * @param SubscriptionPlanInterface $plan
     * @param array $customOptions
     */
    private function setStartEndDate(
        CartItemInterface $item,
        SubscriptionPlanInterface $plan,
        array &$customOptions
    ) {
        list($startDate, $endDate) = $this->startEndDateMapper->getSpecifiedStartEndDates($item);

        if ($startDate) {
            $startDate = $startDate->format('Y-m-d');
        } else {
            $trialDays = $plan->getEnableTrial() ? $plan->getTrialDays() : 0;
            $startDate = $this->date->getDateWithOffsetByDays($trialDays);
        }

        $customOptions[] = [
            'label' => (string)__('Subscription Start Date'),
            'value' => $startDate,
            'print_value' => $startDate,
            'option_type' => 'field',
            'option_view' => false,
        ];

        if ($endDate) {
            $endDate = $endDate->format('Y-m-d');
        } else {
            $endDate = (string)__('N/A (until failed or canceled)');
        }

        $customOptions[] = [
            'label' => (string)__('Subscription End Date'),
            'value' => $endDate,
            'print_value' => $endDate,
            'option_type' => 'field',
            'option_view' => false,
        ];
    }
}
