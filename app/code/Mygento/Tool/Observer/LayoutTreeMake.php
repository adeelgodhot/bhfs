<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
//declare(strict_types=1);

namespace Mygento\Tool\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mygento\Tool\Helper\Data as HelperData;


class LayoutTreeMake implements ObserverInterface
{
    protected $_helperData;

    /**
     * Product constructor.
     */
    public function __construct(
        HelperData $helperData
    ) {
        $this->_helperData = $helperData;
    }

    /**
     * Process event on 'controller_front_send_response_before' event
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->_helperData->isHint()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $response = $observer->getEvent()->getResponse();

        if($this->_helperData->isNoHint()){
            return;
        }

        $content = $response->getContent();
        $this->_helperData->setHtmlContent($content);
        $this->_helperData->makeLayoutTree();

    }
}
