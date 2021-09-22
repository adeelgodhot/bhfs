<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\Quote;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Quote\ItemComparer;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Quote\Validator\StartEndDateValidator;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Amasty\RecurringPayments\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class ItemPlugin
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @codingStandardsIgnoreStart
 */
class ItemPlugin
{
    const PRICE_FIELDS = [
        'price',
        'base_price',
        'custom_price',
        'original_custom_price',
        'price_incl_tax',
        'base_price_incl_tax',
        'row_total',
        'row_total_incl_tax',
        'base_row_total',
        'base_row_total_incl_tax',
        'discount_amount',
        'base_discount_amount',
        'original_price',
        'base_original_price',
    ];

    const TRIAL_CACHE_ID = 'is_trial_item';

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var StartEndDateValidator
     */
    private $startEndDateValidator;

    /**
     * @var ItemComparer
     */
    private $itemComparer;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(
        Json $serializer,
        QuoteValidate $quoteValidate,
        Product $product,
        StartEndDateValidator $startEndDateValidator,
        ItemComparer $itemComparer,
        ItemDataRetriever $itemDataRetriever
    ) {
        $this->serializer = $serializer;
        $this->quoteValidate = $quoteValidate;
        $this->product = $product;
        $this->startEndDateValidator = $startEndDateValidator;
        $this->itemComparer = $itemComparer;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param CartItemInterface $item
     * @param bool $result
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function afterRepresentProduct(CartItemInterface $item, bool $result, ProductInterface $product)
    {
        if ($result) {
            $itemBuyRequest = $item->getBuyRequest();
            if (method_exists($itemBuyRequest, 'getData')) {
                $itemBuyRequest = $itemBuyRequest->getData();
            }
            $productBuyRequest = $this->getProductBuyRequest($product);
            $result = $this->itemComparer->compareWithProduct($itemBuyRequest, $productBuyRequest);
        }

        return $result;
    }

    /**
     * @param AbstractItem $subject
     * @param $key
     * @param null $value
     * @return array
     */
    public function beforeSetData(AbstractItem $subject, $key, $value = null)
    {
        if (!is_string($key)) {
            return [$key, $value];
        }

        if (in_array($key, self::PRICE_FIELDS)) {
            if ($this->isTrialItem($subject)) {
                return [$key, 0];
            }
        }

        return [$key, $value];
    }

    /**
     * @param CartItemInterface $subject
     * @param CartItemInterface $result
     * @return CartItemInterface
     */
    public function afterCheckData(CartItemInterface $subject, $result)
    {
        if (!$subject->getHasError()) {
            $this->startEndDateValidator->validate($subject);
        }
        return $result;
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    private function isTrialItem(AbstractItem $item): bool
    {
        if (!$item->getQuote()) {
            return false;
        }

        if ($item->getQuote()->hasData(QuoteGenerator::GENERATED_FLAG)) {
            return false;
        }

        $isTrial = $item->getData(self::TRIAL_CACHE_ID);
        if ($isTrial !== null) {
            return $isTrial;
        }

        if (!$this->quoteValidate->validateQuoteItem($item)) {
            $item->setData(self::TRIAL_CACHE_ID, false);

            return false;
        }

        $plan = $this->itemDataRetriever->getPlan($item, false);
        $isTrial = $plan->getEnableTrial() && $plan->getTrialDays();
        $item->setData(self::TRIAL_CACHE_ID, $isTrial);

        return $isTrial;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getProductBuyRequest(ProductInterface $product): array
    {
        $buyRequest = $product->getCustomOption('info_buyRequest');

        if ($buyRequest) {
            $buyRequest = $buyRequest->getData();

            return isset($buyRequest['value']) ? $this->serializer->unserialize($buyRequest['value']) : [];
        }

        return [];
    }
}
