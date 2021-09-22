<?php 

namespace Bhfs\CategoriesLinked\Plugin\Magento\Catalog\Model\Layer\Filter;

       class Item
       {
           protected $categoryHelper;
           protected $categoryRepository;

           public function __construct(
               \Magento\Catalog\Helper\Category $categoryHelper,
               \Magento\Catalog\Model\CategoryRepository $categoryRepository
           ) {
               $this->categoryHelper = $categoryHelper;
               $this->categoryRepository = $categoryRepository;
           }

           public function afterGetUrl(
               \Magento\Catalog\Model\Layer\Filter\Item $subject, $result
           ) {
               // custom url for category filter
               if (strtolower($subject->getFilter()->getRequestVar()) === 'cat') {
                   $categoryId = $subject->getValue();
                   $categoryObj = $this->categoryRepository->get($categoryId);
                   return $this->categoryHelper->getCategoryUrl($categoryObj);
               }

               return $result;
           }
       }