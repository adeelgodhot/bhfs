<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class Listing extends DataProvider
{
    protected function searchResultToOutput(SearchResultInterface $searchResult): array
    {
        $labelItemsData = array_reduce($searchResult->getItems(), function (array $carry, DataObject $item): array {
            $carry[] = $item->getData();

            return $carry;
        }, []);

        return [
            'items' => $labelItemsData,
            'totalRecords' => $searchResult->getTotalCount()
        ];
    }
}
