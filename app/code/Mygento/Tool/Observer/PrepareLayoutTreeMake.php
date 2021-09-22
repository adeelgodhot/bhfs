<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//declare(strict_types=1);

namespace Mygento\Tool\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Mygento\Tool\Helper\Data as HelperData;


class PrepareLayoutTreeMake implements ObserverInterface
{
    protected $_helperData;

    /**
     * Helper constructor.
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * Process event on 'core_layout_render_element' event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helperData->isHint()) {
            return;
        }

        $elementName = $observer->getEvent()->getElementName();
        $layout = $observer->getEvent()->getLayout();
        $transport = $observer->getEvent()->getTransport();

        $html = $transport->getData('output');

        $data = ['name' => $elementName, 'html' => $html];
        $this->_helperData->appendNodeToLayoutTree($data);


        if(!$this->_helperData->isNodeInBlockList($elementName)) {
            $id = str_replace(".", "__", $elementName);
            $html = "<div id='dev_hint_layout_{$id}'>{$html}</div>";
            $transport->setData('output', $html);
        }

    }
}
