<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_CategoryCustomUrl
 */
 
namespace EH\CategoryCustomUrl\Rewrite\Catalog\Plugin\Block;

/**
 * Plugin for top menu block
 */
class Topmenu extends \Magento\Catalog\Plugin\Block\Topmenu
{

    /**
     * Get Category Tree
     *
     * @param int $storeId
     * @param int $rootId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryTree($storeId, $rootId)
    {
		$collection = parent::getCategoryTree($storeId, $rootId);
		$collection->addAttributeToSelect('custom_link');
        return $collection;
    }
}
