<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Crawler\HttpClient;

use Amasty\Fpc\Model\ResourceModel\Queue\Page\Collection as PageCollection;

interface CrawlerClientInterface
{
    public function setMethod(string $method);

    public function execute(
        PageCollection $pageCollection,
        array $requestCombinations,
        \Closure $getRequestParams
    ): \Generator;
}
