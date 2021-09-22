<?php
namespace MGS\Mpanel\Block\Widget;
 
class CategoryNavigation extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_categoryHelper;
    protected $categoryFlatConfig;
    protected $topMenu;
	protected $_categoryFactory;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Theme\Block\Html\Topmenu $topMenu
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
		$this->_categoryFactory = $categoryFactory;
        $this->topMenu = $topMenu;
        parent::__construct($context);
    }
	
	
	public function _toHtml()
    {
    	$this->setTemplate('widget/category_navigation.phtml');
		return parent::_toHtml();
    }
	
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }
	
	public function getChildCategoryHtml($childrenCategories,$parentId)
	{
		$html = '';
		$parentId = "sub-menu-" .$parentId;
		if($childrenCategories){
			$html .= '<span class="toggle-icon '. $parentId .'"><a href="javascript:void(0)" onclick="showSubmenu(\''.$parentId.'\');" ></a></span>';
			$html .= '<ul id="'.$parentId.'">';
				foreach($childrenCategories as $childrenCategory){
					if ($childrenCategory->getIsActive()){
						$html .= '<li><a href="' .$this->_categoryHelper->getCategoryUrl($childrenCategory). '">'. $childrenCategory->getName() .'</a>';
						$childrenCategoriess = $this->getChildCategories($childrenCategory);
						if($childrenCategoriess && $this->getSubCategoriesMenu($childrenCategory->getId())){
							$html .= $this->getChildCategoryHtml($childrenCategoriess,$childrenCategory->getId());
						}
						$html .= '</li>';
					}
				}
			$html .= '</ul>';
		}
		return $html;
	}
	
	public function getMgsCategory($categoryId) 
	{
		$this->_category = $this->_categoryFactory->create();
		$this->_category->load($categoryId);		
		return $this->_category;
	}
	public function getSubCategoriesMenu($categories) {
		if($this->getMgsCategory($categories)->getChildren() == null){
			return false;
		};
		return true;
	}
	
	public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
	
	public function getChildCategories($category)
    {
	   if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
			$subcategories = (array)$category->getChildrenNodes();
		} else {
			$subcategories = $category->getChildren();
		}
		return $subcategories;
    }
	public function getTitleBlock()
    {
        if ($this->hasData('title')) {
            return $this->getData('title');
        }
        return;
    }
}