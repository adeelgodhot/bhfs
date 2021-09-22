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

class MobileCombination implements CombinationSourceInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(Config $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function getVariations(): array
    {
        return $this->configProvider->isProcessMobile() ? [true, false] : [];
    }

    public function getCombinationKey(): string
    {
        return 'crawler_mobile';
    }

    public function modifyRequest(array $combination, array &$requestParams, Context $context)
    {
        if ($isMobile = $combination[$this->getCombinationKey()] ?? null) {
            $requestParams[RequestOptions::HEADERS]['User-Agent'] = $this->configProvider->getMobileAgent();
        }
    }

    public function prepareLog(array $crawlerLogData, array $combination): array
    {
        if ($isMobile = $combination[$this->getCombinationKey()] ?? null) {
            $crawlerLogData['mobile'] = $isMobile;
        }

        return $crawlerLogData;
    }
}
