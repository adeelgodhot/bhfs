<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Source\Provider;

class CompositeSource implements SourceProviderInterface
{
    /**
     * @var SimpleSource
     */
    private $simpleSourceProvider;

    /**
     * @var array
     */
    private $sourceTypeIds;

    public function __construct(
        SimpleSource $simpleSourceProvider,
        array $sourceTypeIds = []
    ) {
        $this->simpleSourceProvider = $simpleSourceProvider;
        $this->sourceTypeIds = $sourceTypeIds;
    }

    public function getPagesBySourceType(int $sourceType, int $pagesLimit): array
    {
        $pages = [];

        foreach ($this->sourceTypeIds as $sourceTypeId) {
            $pagesBatch = $this->simpleSourceProvider->getPagesBySourceType((int)$sourceTypeId, $pagesLimit);
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge.ForeachArrayMerge
            $pages = array_merge($pages, $pagesBatch);
            $pagesLimit -= count($pagesBatch);

            if ($pagesLimit <= 0) {
                break;
            }
        }

        return $pages;
    }
}
