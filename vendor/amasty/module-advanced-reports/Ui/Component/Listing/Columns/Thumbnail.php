<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Ui\Component\Listing\Columns;

class Thumbnail extends \Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail
{
    public function prepareDataSource(array $dataSource)
    {
        $data = parent::prepareDataSource($dataSource);

        if (!isset($dataSource['data']['items'])) {
            return $data;
        }

        foreach ($data['data']['items'] as &$item) {
            $item['thumbnail_link'] = $this->urlBuilder->getUrl(
                'catalog/product/edit',
                ['id' => $item['product_id'] ?? 0, 'store' => $this->context->getRequestParam('store')]
            );
        }

        return $data;
    }
}
