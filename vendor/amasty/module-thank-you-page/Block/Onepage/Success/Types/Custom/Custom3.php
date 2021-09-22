<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success\Types\Custom;

use Amasty\ThankYouPage\Block\Onepage\Success\Types\CustomAbstract;

/**
 * Abstraction for Header and custom blocks
 */
class Custom3 extends CustomAbstract
{
    const BLOCK_CONFIG_NAME = 'custom3';

    /**
     * Related group name in admin settings
     *
     * @return string
     */
    protected function getGroupPrefix()
    {
        return 'block_' . self::BLOCK_CONFIG_NAME;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->blockConfig->getWidthByBlockId(self::BLOCK_CONFIG_NAME);
    }
}
