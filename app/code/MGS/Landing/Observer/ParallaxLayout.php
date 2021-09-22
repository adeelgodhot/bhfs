<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Landing\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class ParallaxLayout implements ObserverInterface {
    
    private $registry;

    public function __construct(Registry $registry) 
    {
        $this->registry = $registry;
    }
    
    public function execute(Observer $observer) {
        
        /** @var \Magento\Framework\View\Layout $layout */

        $layout = $observer->getLayout();
        
        $action = $observer->getData('full_action_name');
        if ($action != 'catalog_category_view')
            return $this;
        
        $category = $this->registry->registry('current_category');
        
        if (!$category)
            return $this;
        
        if($category->getIsLanding() && $category->getCateLandingType() == 3){
            $layout->getUpdate()->addHandle('catalog_category_parallax');
        }
    }
}
