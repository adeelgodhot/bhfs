<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//declare(strict_types=1);

namespace Mygento\Tool\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;


class BlockTreeMake implements ObserverInterface
{
    /**
     * @var FullActionName
     */
    private $fullActionName;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * Product constructor.
     * @param String $fullActionName
     * @param Magento\Framework\View\Layout $layout
     */
    /*public function __construct(
        $fullActionName,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->fullActionName = $fullActionName;
        $this->layout = $layout;
    }*/

    /**
     * Process event on 'layout_generate_blocks_after' event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $layout \Magento\Framework\View\Layout */
        /*$layout = $observer->getEvent()->getLayout();
        $fullActionName = $observer->getEvent()->getFullActionName();*/

        $elementName = $observer->getEvent()->getElementName();
        $layout = $observer->getEvent()->getLayout();
        $transport = $observer->getEvent()->getTransport();

        $block = $layout->getBlock($elementName);
        $template = $block->getTemplate();
        $templateFile = $block->getTemplateFile();

        $html = $transport->getData('output');

    }
}
