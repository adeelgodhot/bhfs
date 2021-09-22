<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue\Combination\Context;

use Amasty\Fpc\Helper\Http as HttpHelper;
use Amasty\Fpc\Model\Config;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreManagerInterface;

class CurrencyCombination implements CombinationSourceInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var string
     */
    private $defaultCurrency;

    public function __construct(
        Config $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->defaultCurrency = $storeManager->getWebsite()->getDefaultStore()->getDefaultCurrency()->getCode();
    }

    public function getVariations(): array
    {
        $currencies = $this->configProvider->getCurrencies();
        $defaultCurrencyKey = array_search($this->defaultCurrency, $currencies);

        if (false !== $defaultCurrencyKey) {
            unset($currencies[$defaultCurrencyKey]);
            array_unshift($currencies, null);
        }

        return $currencies;
    }

    public function getCombinationKey(): string
    {
        return 'crawler_currency';
    }

    public function modifyRequest(array $combination, array &$requestParams, Context $context)
    {
        if ($currency = $combination[$this->getCombinationKey()] ?? null) {
            $requestParams[RequestOptions::HEADERS][HttpHelper::CURRENCY_HEADER] = $currency;
            $context->setValue(
                Context::CONTEXT_CURRENCY,
                $currency,
                $this->defaultCurrency
            );
        }
    }

    public function prepareLog(array $crawlerLogData, array $combination): array
    {
        if ($currency = $combination[$this->getCombinationKey()] ?? null) {
            $crawlerLogData['currency'] = $currency;
        }

        return $crawlerLogData;
    }
}
