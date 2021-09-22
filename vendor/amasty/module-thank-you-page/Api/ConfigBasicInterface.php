<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Api;

interface ConfigBasicInterface
{

    /**
     * @return bool
     */
    public function isBlockEnabled();

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function setGroupPrefix($prefix);
}
