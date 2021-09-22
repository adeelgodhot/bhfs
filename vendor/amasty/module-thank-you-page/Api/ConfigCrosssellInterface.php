<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Api;

interface ConfigCrosssellInterface extends ConfigBasicInterface
{

    /**
     * @return string
     */
    public function getProductLimit();

    /**
     * @return bool
     */
    public function isShowOutOfStock();
}
