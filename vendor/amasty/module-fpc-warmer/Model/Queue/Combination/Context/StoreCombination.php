<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue\Combination\Context;

use Amasty\Fpc\Model\Config;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Http\Context;
use Magento\Store\Model\StoreCookieManager;
use Magento\Store\Model\StoreManagerInterface;

class StoreCombination implements CombinationSourceInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    private $defaultStoreCode;

    public function __construct(
        Config $configProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->defaultStoreCode = $this->storeManager->getDefaultStoreView()->getCode();
    }

    public function getVariations(): array
    {
        return $this->configProvider->getStores();
    }

    public function getCombinationKey(): string
    {
        return 'crawler_store';
    }

    public function modifyRequest(array $combination, array &$requestParams, Context $context)
    {
        if ($storeId = $combination[$this->getCombinationKey()] ?? null) {
            $storeCode = $this->storeManager->getStore($storeId)->getCode();

            if ($storeCode === $this->defaultStoreCode) {
                return;
            }

            $requestParams[RequestOptions::COOKIES][StoreCookieManager::COOKIE_NAME] = $storeCode;
            $context->setValue(
                StoreManagerInterface::CONTEXT_STORE,
                $storeCode,
                $this->defaultStoreCode
            );
        }
    }

    public function prepareLog(array $crawlerLogData, array $combination): array
    {
        if ($storeId = $combination[$this->getCombinationKey()] ?? null) {
            $crawlerLogData['store'] = $storeId;
        }

        return $crawlerLogData;
    }
}
