<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */

declare(strict_types=1);

namespace Amasty\PageSpeedTools\Model\Output;

class CheckIsOutputHtmlProcessor implements OutputProcessorInterface
{
    public function process(string &$output): bool
    {
        if (preg_match('/(<html[^>]*>)(?>.*?<body[^>]*>)/is', $output)) {
            if (preg_match('/(<\/body[^>]*>)(?>.*?<\/html[^>]*>)$/is', $output)) {
                return true;
            }
        }

        return false;
    }
}
