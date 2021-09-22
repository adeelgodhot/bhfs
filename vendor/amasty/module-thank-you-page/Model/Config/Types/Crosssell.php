<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Config\Types;

use Amasty\ThankYouPage\Api\ConfigCrosssellInterface;

class Crosssell extends Basic implements ConfigCrosssellInterface
{

    /**#@+
     * xpath field parts
     */
    const FIELD_PRODUCT_LIMIT = 'limit';
    const FIELD_SHOW_OUT_OF_STOCK = 'show_out_of_stock';
    const BLOCK_CONFIG_NAME = 'crosssell';

    /**#@-*/

    /**
     * @return string
     */
    public function getProductLimit()
    {
        return $this->getValue($this->getGroupPrefix() . self::FIELD_PRODUCT_LIMIT);
    }

    /**
     * @return string
     */
    public function isShowOutOfStock()
    {
        return $this->getValue($this->getGroupPrefix() . self::FIELD_SHOW_OUT_OF_STOCK);
    }
}
