<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Block\Product\Content\View\Options\Type\Select;

use Magento\Framework\Data\CollectionDataSourceInterface;

class Multiple extends AbstractSelect implements CollectionDataSourceInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Amp::product/content/view/options/type/select/multiple.phtml';
}
