<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Landing\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class RemoveFilter implements ObserverInterface {
    
    private $registry;

    public function __construct(Registry $registry) 
    {
        $this->registry = $registry;
    }
    
    public function execute(Observer $observer) {
        
        /** @var \Magento\Framework\View\Layout $layout */

        $layout = $observer->getLayout();
        
        $block = $layout->getBlock('category.products');
        
        if($block) {
            $category = $this->registry->registry('current_category');
            if($category->getIsLanding()){
                $layout->unsetElement('catalog.leftnav');
            }
        }
    }
}
