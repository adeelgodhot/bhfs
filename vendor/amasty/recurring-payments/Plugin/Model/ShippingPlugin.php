<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model;

use Amasty\RecurringPayments\Model\Config;
use Magento\Shipping\Model\Shipping;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Amasty\RecurringPayments\Model\RequestProcessor;

class ShippingPlugin
{
    const FREE_PRICE = 0;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var RequestProcessor
     */
    private $requestProcessor;

    public function __construct(Config $config, RequestProcessor $requestProcessor)
    {
        $this->config = $config;
        $this->requestProcessor = $requestProcessor;
    }

    /**
     * @param Shipping $subject
     * @param callable $proceed
     * @param RateRequest $request
     *
     * @return Shipping
     */
    public function aroundCollectRates(Shipping $subject, callable $proceed, RateRequest $request)
    {
        if (!$this->needProcessFreeShipping($request)) {
            return $proceed($request);
        }

        $isExistUsualProduct = false;
        $this->requestProcessor->process($request, $isExistUsualProduct);

        /** @var Shipping $originalMethodResult */
        $originalMethodResult = $proceed($request);

        if (!$isExistUsualProduct) {
            // fix for magento 2.3.4 (see \Magento\Shipping\Model\Rate\CarrierResult)
            $subject->getResult()->getAllRates();

            //Save original result for correct return.
            $originalResult = clone $subject->getResult();
            $this->resetShippingPrices($originalResult->getAllRates());

            $originalMethodResult->getResult()->reset();
            $originalMethodResult->getResult()->append($originalResult);
        }

        return $originalMethodResult;
    }

    /**
     * @param array $rates
     */
    private function resetShippingPrices(array $rates)
    {
        /** @var Method $rate */
        foreach ($rates as $rate) {
            if ($rate instanceof Error) {
                continue;
            }

            $rate->setPrice(self::FREE_PRICE);
        }
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    private function needProcessFreeShipping(RateRequest $request): bool
    {
        $hasGeneratedSubscriptionProducts = false;
        $isEnabledFreeShipping = false;
        foreach ($request->getAllItems() as $item) {
            $buyRequest = $item->getBuyRequest();
            if (isset($buyRequest['subscription_product'])) {
                $hasGeneratedSubscriptionProducts = true;
                if (!empty($buyRequest['free_shipping'])) {
                    $isEnabledFreeShipping = true;
                }
            }
        }

        if ($hasGeneratedSubscriptionProducts) {
            $needProcess = $isEnabledFreeShipping;
        } else {
            $needProcess = $this->config->isEnableFreeShipping();
        }

        return $needProcess;
    }
}
