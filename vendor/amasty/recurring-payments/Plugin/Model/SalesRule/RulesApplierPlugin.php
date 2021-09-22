<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\SalesRule;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\Amount;
use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Product as RecurringProduct;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Quote\Discount;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\RulesApplier;

class RulesApplierPlugin
{
    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var RecurringProduct
     */
    private $recurringProduct;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        Amount $amount,
        QuoteValidate $quoteValidate,
        RecurringProduct $recurringProduct,
        ItemDataRetriever $itemDataRetriever,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Amasty\Base\Model\MagentoVersion $magentoVersion
    ) {
        $this->amount = $amount;
        $this->quoteValidate = $quoteValidate;
        $this->recurringProduct = $recurringProduct;
        $this->itemDataRetriever = $itemDataRetriever;
        $this->priceCurrency = $priceCurrency;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * @param RulesApplier $subject
     * @param AbstractItem $item
     * @param Collection $rules
     * @param bool $skipValidation
     * @param $couponCode
     *
     * @return array
     */
    public function beforeApplyRules(
        RulesApplier $subject,
        AbstractItem $item,
        Collection $rules,
        bool $skipValidation,
        $couponCode
    ) {
        $baseDiscountAmount = null;
        $buyRequest = $item->getBuyRequest();
        if (isset($buyRequest['base_discount_amount'])) {
            $baseDiscountAmount = (float)$buyRequest['base_discount_amount'];
            $couponCode = null;
        } elseif ($this->quoteValidate->validateQuoteItem($item)) {
            $couponCode = null;
            $plan = $this->itemDataRetriever->getPlan($item, false);
            if (!$item->getQuote()->getData(QuoteGenerator::DISABLE_DISCOUNT_FLAG)
                && $plan->getEnableDiscount()
                && $plan->getDiscountAmount()
                && !$this->skipEstimationDiscount($item, $plan)
            ) {
                $baseDiscountAmount = $this->amount->getAmount(
                    $item->getProduct(),
                    (float)$plan->getDiscountAmount(),
                    $plan->getDiscountType(),
                    $item
                );
            }
        }

        if (!empty($baseDiscountAmount)) {
            $baseDiscountAmount = min($baseDiscountAmount, $item->getBaseRowTotal());
            $item->setBaseDiscountAmount($baseDiscountAmount);
            $item->setDiscountAmount($this->amount->convert($baseDiscountAmount));
            $magentoVersion = $this->magentoVersion->get();
            // magento 2.4.0 for bundles have only child discount
            if (version_compare($magentoVersion, '2.4.0', '>=')
                && $item->getHasChildren()
                && $item->isChildrenCalculated()
            ) {
                $this->distributeDiscount($item);
            }
        }

        return [$item, $rules, $skipValidation, $couponCode];
    }

    /**
     * Distribute discount at parent item to children items
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return $this
     */
    private function distributeDiscount(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $parentBaseRowTotal = $item->getBaseRowTotal();
        $keys = [
            'discount_amount',
            'base_discount_amount',
            'original_discount_amount',
            'base_original_discount_amount',
        ];
        $roundingDelta = [];
        foreach ($keys as $key) {
            //Initialize the rounding delta to a tiny number to avoid floating point precision problem
            $roundingDelta[$key] = 0.0000001;
        }
        foreach ($item->getChildren() as $child) {
            $ratio = $parentBaseRowTotal != 0 ? $child->getBaseRowTotal() / $parentBaseRowTotal : 0;
            foreach ($keys as $key) {
                if (!$item->hasData($key)) {
                    continue;
                }
                $value = $item->getData($key) * $ratio;
                $roundedValue = $this->priceCurrency->round($value + $roundingDelta[$key]);
                $roundingDelta[$key] += $value - $roundedValue;
                $child->setData($key, $roundedValue);
            }
        }

        foreach ($keys as $key) {
            $item->unsetData($key);
        }
        return $this;
    }

    /**
     * Disable discount for quote estimation if the only discount cycle is used on checkout
     * @param AbstractItem $item
     * @param SubscriptionPlanInterface $plan
     * @return bool
     */
    private function skipEstimationDiscount(AbstractItem $item, SubscriptionPlanInterface $plan): bool
    {
        if (!$item->getQuote()->getData(QuoteGenerator::GENERATED_FLAG)) {
            return false;
        }

        if ($plan->getEnableTrial() && $plan->getTrialDays()) {
            return false;
        }

        return $plan->getEnableDiscountLimit()
            && $plan->getNumberOfDiscountCycles() <= 1;
    }
}
