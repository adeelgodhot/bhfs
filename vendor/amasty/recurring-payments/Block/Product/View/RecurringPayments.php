<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Product\View;

use Amasty\RecurringPayments\Model\Amount;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Label;
use Amasty\RecurringPayments\Model\Product;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayUtils;

class RecurringPayments extends \Magento\Catalog\Block\Product\View\AbstractView
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var Label
     */
    private $label;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        Product $product,
        Amount $amount,
        Label $label,
        Config $configProvider,
        Json $jsonSerializer,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        array $data = []
    ) {
        parent::__construct($context, $arrayUtils, $data);
        $this->product = $product;
        $this->amount = $amount;
        $this->label = $label;
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        $product = $this->getProduct();
        $previousData = $this->jsLayout['components']['recurringPaymentsProvider'] ?? [];
        $this->jsLayout['components']['recurringPaymentsProvider'] = array_merge(
            $previousData,
            [
                'plans' => $this->getPlansData($product),
                'dateFormat' => 'yyyy-MM-dd',
                'originalPrice' => $this->amount->convertAndFormat((float)$product->getPrice()),
                'isFreeShipping' => $this->configProvider->isEnableFreeShipping(),
                'isAllowSpecifyStartEndDate' => $this->configProvider->isAllowSpecifyStartEndDate(),
                'isSubscriptionOnly' => $this->isSubscriptionOnly($product),
            ]
        );

        return $this->jsonSerializer->serialize($this->jsLayout);
    }

    /**
     * @param MagentoProduct $product
     * @return array
     */
    public function getPlansData($product)
    {
        $plans = $this->product->getActiveSubscriptionPlans($product);
        $data = [];

        foreach ($plans as $plan) {
            $initialFeeAmount = $this->amount->getAmount(
                $product,
                (float)$plan->getInitialFeeAmount(),
                $plan->getInitialFeeType()
            );

            $discountAmount = $this->amount->getAmount(
                $product,
                (float)$plan->getDiscountAmount(),
                $plan->getDiscountType()
            );

            $planName = $this->billingFrequencyLabelMapper->getLabel(
                $plan->getFrequency(),
                $plan->getFrequencyUnit()
            );

            $data[] = [
                'plan_id' => $plan->getPlanId(),
                'plan_name' => $planName,
                'frequency' => $plan->getFrequency(),
                'frequency_unit' => $plan->getFrequencyUnit(),
                'is_enable_trial' => $plan->getEnableTrial() && $plan->getTrialDays() > 0,
                'trial_days' => $plan->getTrialDays(),
                'is_enable_fee' => $plan->getEnableInitialFee() && $plan->getInitialFeeAmount(),
                'fee_type' => $plan->getInitialFeeType(),
                'fee_amount' => $plan->getInitialFeeAmount(),
                'fee_amount_formatted' =>  $this->amount->convertAndFormat($initialFeeAmount),
                'discount_enabled' => $plan->getEnableDiscount() && $plan->getDiscountAmount(),
                'discount_amount_type' => $plan->getDiscountType(),
                'discount_amount' => $plan->getDiscountAmount(),
                'discount_amount_formatted' => $this->amount->convertAndFormat($discountAmount),
                'discount_cycles_limit_enabled' => (bool)$plan->getEnableDiscountLimit(),
                'number_discount_cycles' => (int)$plan->getNumberOfDiscountCycles(),
            ];
        }

        return $data;
    }

    /**
     * @param MagentoProduct $product
     *
     * @return bool
     */
    public function isRecurringEnable(MagentoProduct $product): bool
    {
        return $this->product->isRecurringEnable($product);
    }

    /**
     * @param MagentoProduct $product
     *
     * @return bool
     */
    public function isSubscriptionOnly(MagentoProduct $product): bool
    {
        return $this->product->isSubscriptionOnly($product);
    }

    /**
     * @return string
     */
    public function getSinglePurchaseLabel(): string
    {
        return $this->label->getSinglePurchaseLabel();
    }

    /**
     * @return string
     */
    public function getSingleRecurringLabel(): string
    {
        return $this->label->getSingleRecurringLabel();
    }
}
