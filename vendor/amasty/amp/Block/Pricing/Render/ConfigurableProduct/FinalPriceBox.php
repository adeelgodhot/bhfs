<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Block\Pricing\Render\ConfigurableProduct;

use Amasty\Amp\Model\UrlConfigProvider;

class FinalPriceBox extends \Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox
{
    /**
     * @return string[]
     */
    public function getCacheKeyInfo()
    {
        return [$this->getNameInLayout(), UrlConfigProvider::AMP];
    }
}
