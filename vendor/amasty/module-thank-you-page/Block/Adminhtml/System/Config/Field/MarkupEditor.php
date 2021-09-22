<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */


namespace Amasty\ThankYouPage\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class MarkupEditor extends Field
{
    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $html = <<<HTML
    Use dot <b>.</b> as a row separator and comma 
    <b>,</b> as a column separator. 
    Either of these symbols might always be inserted after each variable name to structurize the page. Spaces are not allowed. 
    All available variables associated with Thank You Page blocks are listed below: 
    <br>&bull; <i>{{header}}</i>
    <br>&bull; <i>{{order_review}}</i>
    <br>&bull; <i>{{custom1}}</i>
    <br>&bull; <i>{{custom2}}</i>
    <br>&bull; <i>{{custom3}}</i>
    <br>&bull; <i>{{newsletter}}</i>
    <br>&bull; <i>{{cross_sell}}</i>
    <br>&bull; <i>{{create_account}}</i>
    <br>Make sure that the block you want to use is enabled, otherwise it won't be displayed. 
    If the block is enabled but the corresponding variable isn't inserted in the text editor, it won't be displayed either. 
    Double braces around variable names should not be omitted.
HTML;

        $element->setComment($html);

        return parent::render($element);
    }
}
