<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Simplexml;

use Magento\Framework\Simplexml\Config as SimplexmlConfig;

class Config extends SimplexmlConfig
{
    /**
     * Converts meaningful xml characters to xml entities
     *
     * @param string $text
     * @return string
     */
    public function processFileData($text)
    {
        $text = str_replace('&amp;', '&', $text);
        $text = str_replace('&', '&amp;', $text);

        return $text;
    }
}
