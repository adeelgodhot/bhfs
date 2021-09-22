<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Crawler;

use Amasty\Fpc\Model\Log;
use Amasty\Fpc\Model\Queue\Combination;
use Amasty\Fpc\Model\Queue\Page;
use Amasty\Fpc\Model\ResourceModel\Queue\Page\Collection as PageCollection;
use GuzzleHttp\RequestOptions;
use Magento\Framework\App\Http\ContextFactory;
use Magento\Framework\App\Response\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Session\SessionManagerInterface;

class Crawler
{
    /**
     * @var Log
     */
    private $crawlerLog;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var Combination\Provider
     */
    private $combinationProvider;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var Request\DefaultParamProvider
     */
    private $defaultParamProvider;

    public function __construct(
        Log $crawlerLog,
        ClientFactory $clientFactory,
        ContextFactory $contextFactory,
        Combination\Provider $combinationProvider,
        SessionManagerInterface $sessionManager,
        Request\DefaultParamProvider $defaultParamProvider
    ) {
        $this->crawlerLog = $crawlerLog;
        $this->clientFactory = $clientFactory;
        $this->contextFactory = $contextFactory;
        $this->combinationProvider = $combinationProvider;
        $this->sessionManager = $sessionManager;
        $this->defaultParamProvider = $defaultParamProvider;
    }

    public function processPages(PageCollection $pageCollection): int
    {
        $pagesProcessed = 0;
        $this->crawlerLog->trim();
        $client = $this->clientFactory->create();
        $requestCombinations = $this->combinationProvider->getCombinations();
        $combinationSources = $this->combinationProvider->getCombinationSources();
        $requestParamsClosure = \Closure::fromCallable([$this, 'buildRequestParams']);

        /** @var DataObject $responseData */
        foreach ($client->execute($pageCollection, $requestCombinations, $requestParamsClosure) as $responseData) {
            /** @var Page $page */
            if ($page = $responseData->getPage()) {
                $combination = $responseData->getCombination();
                $crawlerLogData = [
                    'url' => $page->getUrl(),
                    'rate' => $page->getRate(),
                    'status' => $responseData->getStatus(),
                    'load_time' => round($responseData->getLoadTime() ?? 0)
                ];

                /** @var Combination\Context\CombinationSourceInterface $source */
                foreach ($combinationSources as $source) {
                    $crawlerLogData = $source->prepareLog($crawlerLogData, $combination);
                }

                $this->crawlerLog->add($crawlerLogData);
                $pagesProcessed++;
            }
        }

        return $pagesProcessed;
    }

    public function buildRequestParams(Page $page, array $combination)
    {
        $httpContext = $this->contextFactory->create();
        $combinationSources = $this->combinationProvider->getCombinationSources();
        $requestParams = $this->defaultParamProvider->getDefaultParams();
        $requestParams[RequestOptions::HEADERS][RegistryConstants::CRAWLER_URL_HEADER] = rtrim($page->getUrl(), '/');

        /** @var Combination\Context\CombinationSourceInterface $source */
        foreach ($combinationSources as $source) {
            $source->modifyRequest($combination, $requestParams, $httpContext);
        }

        if ($varyString = $httpContext->getVaryString()) {
            $requestParams[RequestOptions::COOKIES][Http::COOKIE_VARY_STRING] = $varyString;
        }

        /**
         * Combine all cookie data into single CookieJar object
         */
        $requestParams[RequestOptions::COOKIES] = \GuzzleHttp\Cookie\CookieJar::fromArray(
            $requestParams[RequestOptions::COOKIES],
            $this->resolveCookieDomain((string)$page->getUrl())
        );
        $requestParams[RequestOptions::HEADERS]['User-Agent'] .= ' ' . RegistryConstants::CRAWLER_AGENT_EXTENSION;

        return $requestParams;
    }

    private function resolveCookieDomain(string $pageUrl): string
    {
        if (!$this->sessionManager->getCookieDomain()) {
            preg_match('/^https?\:\/\/(?<domain>[^\/?#]+)(?:[\/?#]|$)/', $pageUrl, $matches);

            return $matches['domain'] ?? '';
        }

        return $this->sessionManager->getCookieDomain();
    }
}
