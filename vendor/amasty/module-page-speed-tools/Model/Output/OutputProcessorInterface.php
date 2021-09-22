<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */

declare(strict_types=1);

namespace Amasty\PageSpeedTools\Model\Output;

interface OutputProcessorInterface
{
    /**
     * @param string &$output
     *
     * @return bool
     */
    public function process(string &$output): bool;
}
