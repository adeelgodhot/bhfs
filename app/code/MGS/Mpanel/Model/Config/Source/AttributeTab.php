<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\Mpanel\Model\Config\Source;

class AttributeTab implements \Magento\Framework\Option\ArrayInterface
{
	public function __construct(
		\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollection
    ) {
		$this->_attributeCollection = $attributeCollection;
    }
	
	public function getAttributeCollection(){
		return $this->_attributeCollection->create()->addVisibleFilter();
	}
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		$attrs = [['value' => '', 'label' => __('')]];
		
		$attributes = $this->getAttributeCollection()
			->addFieldToFilter('backend_type', 'int')
			->addFieldToFilter('frontend_input', 'boolean');
		
		if(count($attributes)>0){
			foreach ($attributes as $productAttr) { 
				$attrs[] = ['value'=>$productAttr->getAttributeCode(), 'label'=>$productAttr->getFrontendLabel()];
			}
		}
		$attrs[] = ['value' => 'mgs_tab_new', 'label' => __('New Product')];
		$attrs[] = ['value' => 'mgs_tab_sale', 'label' => __('Sale Price')];
		$attrs[] = ['value' => 'mgs_tab_top_rate', 'label' => __('Top Rate')];
        return $attrs;
    }
}
