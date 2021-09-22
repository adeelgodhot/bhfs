<?php
/**
 * Public alias for the application entry point
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

try {
    require __DIR__ . '/../../app/bootstrap.php';
} catch (\Exception $e) {
    echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Autoload error</h3>
    </div>
    <p>{$e->getMessage()}</p>
</div>
HTML;
    exit(1);
}

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

$categoryFactory = $objectManager->get('Magento\Catalog\Model\CategoryFactory');


$xmldata = \simplexml_load_file("ProductCategoriesData_Test.xml") or die("Failed to load");

foreach($xmldata->children() as $empl) {  

    $sku = $empl->sku;
    $categories = $empl->Department; 
    $categoryIds = array();

    // detect if product placed in multiple categories
    $categoriesArray = explode('|', $categories);

    foreach($categoriesArray as $item) { 

        // get last category in the tree
        $categoryTree = explode('/', $item);
        $categoryTitleParent = $categoryTree[count($categoryTree)-2]; 
        $categoryTitleChild = end($categoryTree);


        $collectionParent = $categoryFactory->create()->getCollection()
              ->addAttributeToFilter('name',$categoryTitleParent)->setPageSize(1);

        if ($collectionParent->getSize()) {
            $parentId = $collectionParent->getFirstItem()->getId();
        }


        $collection = $categoryFactory->create()->getCollection()
              ->addAttributeToFilter('name',$categoryTitleChild)->setPageSize(5);


        if ($collection->getSize()) {
            foreach($collection as $category) { 
                $categoryId = $category->getId();
                $parentCategoryId = $category->getParentId();
                

                if( $parentId == $parentCategoryId ){

                 array_push($categoryIds, $categoryId);

                 echo "$sku, $parentCategoryId, $categoryTitleParent > $categoryTitleChild [$categoryId] <br/>"; 

                }
                 
            }

        }

          

    } 
    if (!empty($categoryIds)) {
        try {

            echo "<pre>";
            print_r($categoryIds);
            echo "</pre>";

            

            $categoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');

            $categoryLinkRepository->assignProductToCategories($sku, $categoryIds);
         
        } catch (\Exception $e) {
            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";
        }
    }
} 