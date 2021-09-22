<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue\Combination\Context;

use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\Crawler\RegistryConstants;
use Amasty\Fpc\Model\Debug\ContextDebugService;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Http\Context;

class DebugCombination implements CombinationSourceInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var ContextDebugService
     */
    private $contextDebugService;

    public function __construct(
        Config $configProvider,
        ContextDebugService $contextDebugService
    ) {
        $this->configProvider = $configProvider;
        $this->contextDebugService = $contextDebugService;
    }

    public function getVariations(): array
    {
        return [];
    }

    public function getCombinationKey(): string
    {
        return 'crawler_debug_context';
    }

    public function modifyRequest(array $combination, array &$requestParams, Context $context)
    {
        if ($this->configProvider->isDebugContext()) {
            $url = (string)$requestParams[RequestOptions::HEADERS][RegistryConstants::CRAWLER_URL_HEADER] ?? '';

            if ($url) {
                $vary = $context->getVaryString(); // Force run plugin chain over getVaryString method
                $contextDefaultData = $context->toArray()['default'];
                ksort($contextDefaultData);
                $debugData = [
                    'context' => ['vary' => $vary] + $context->getData(),
                    'defaults' => $contextDefaultData,
                ];
                $this->contextDebugService->addDebugLog($url, $debugData);
            }
        }
    }

    public function prepareLog(array $crawlerLogData, array $combination): array
    {
        return $crawlerLogData;
    }
}
