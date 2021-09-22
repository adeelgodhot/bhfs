<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class QuerySource implements OptionSourceInterface
{
    const SOURCE_ALL_PAGES = 0;
    const SOURCE_SITE_MAP = 1;
    const SOURCE_TEXT_FILE = 2;
    const SOURCE_SITE_MAP_AND_TEXT_FILE = 3;
    const SOURCE_ACTIVITY = 4;
    const SOURCE_COMBINE_TEXT_FILE_AND_PAGE_TYPES = 5;

    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'label' => __('Pages Types'),
            'value' => self::SOURCE_ALL_PAGES
        ];

        $options[] = [
            'label' => __('Text file with one link per line'),
            'value' => self::SOURCE_TEXT_FILE
        ];

        $options[] = [
            'label' => __('Sitemap XML'),
            'value' => self::SOURCE_SITE_MAP
        ];

        $options[] = [
            'label' => __('Sitemap XML and Text File together'),
            'value' => self::SOURCE_SITE_MAP_AND_TEXT_FILE
        ];

        $options[] = [
            'label' => __('Customers Activity Source'),
            'value' => self::SOURCE_ACTIVITY
        ];

        $options[] = [
            'label' => __('Page Types and Text File Together'),
            'value' => self::SOURCE_COMBINE_TEXT_FILE_AND_PAGE_TYPES
        ];

        return $options;
    }
}
