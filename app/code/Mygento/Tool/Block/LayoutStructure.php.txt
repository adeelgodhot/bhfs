<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mygento\Tool\Block;


/**
 * Layout structure block
 *
 * @api
 * @since 100.0.2
 */
class LayoutStructure extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->setTemplate('Mygento_Tool::layout_structure.phtml');
        parent::__construct($context, $data);
    }


}
