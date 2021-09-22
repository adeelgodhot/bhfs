<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Block\Adminhtml\Settings;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ClearWebpFolderButton extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setData('value', __("Clear WebP Folder"));
        $element->setData('class', "action-default amoptimizer-btn");
        $element->setData('onclick', "location.href = '" . $this->getActionUrl() . "'");

        return parent::_getElementHtml($element);
    }

    public function getActionUrl(): string
    {
        return $this->_urlBuilder->getUrl('amimageoptimizer/image/clearWebpFolder');
    }
}
