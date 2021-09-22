<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success\Types;

/**
 * Header block
 */
interface TypesInterface
{

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function toHtml();
}
