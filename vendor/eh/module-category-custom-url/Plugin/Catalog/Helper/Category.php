<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_CategoryCustomUrl
 */
 
namespace EH\CategoryCustomUrl\Plugin\Catalog\Helper;

class Category
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

	public function aroundGetCategoryUrl(
		\Magento\Catalog\Helper\Category $subject,
		\Closure $proceed,
		$category
	) {
		$result = $proceed($category);

		if ($customLink = $category->getCustomLink()) {
            if (strpos($customLink, '://') !== false) {
                return $customLink;
            }
            return $this->storeManager->getStore()->getBaseUrl().$customLink;
		}	

		return $result;
	}
}
