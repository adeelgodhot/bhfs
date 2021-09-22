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

$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');

$xmlRootPath  =  $directory->getPath('pub') . "/clf/";

$product = $objectManager->get('Magento\Catalog\Model\Product');

$collectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

$productCollection = $collectionFactory->create();
/** Apply filters here */
$productCollection->addAttributeToSelect('*');
// It will return boolean value for Yes/NO. "1" means "Yes" and "0" means "No"
$productCollection->addFieldToFilter('restrict_editing', 1);
// Don't have to do this
// $productCollection->load();

// create empty array to store our new products
$elementsToDelete = array();
$documentsToClean = array("ProductData.xml", "ProductExtendedData.xml", "ProductAttributesData.xml");
// $documentsToClean = array("ProductData_Test.xml");

foreach ($productCollection as $product){

    // echo 'Name  =  ' . $product->getName() . '<br>';
    $product_sku = $product->getSku();
    // build array
    $elementsToDelete[] = $product_sku;
}

if (!empty($elementsToDelete)) {

    // we need to clean up ProductData.xml and ProductAttributesData.xml
    // remove all the nodes that have the same skus

    // echo "<pre>";
    // print_r($elementsToDelete);
    // echo "</pre>";
    
    $index = 0;

    foreach ($documentsToClean as $xmlDocument) {
        echo "Clean $xmlDocument \n";

        $doc = new DOMDocument;

        $doc->load($xmlRootPath . $xmlDocument);

        switch ($index) {
            case 0:
                $domNodeList = $doc->getElementsByTagName('Product');
                break;
            case 1:
               $domNodeList = $doc->getElementsByTagName('ProductData');
                break;
            case 2:
                $domNodeList = $doc->getElementsByTagName('ProductAttribute');
                break;
        }

        $domArray = array(); //set up an array to catch all our nodes

        foreach($domNodeList as $dom) {
            $domArray[] = $dom;
        }

        // loop through the array and delete each node
        foreach($domArray as $node){

            $sku = trim(strtok($node->nodeValue, "\n"));

            if(in_array($sku, $elementsToDelete)){
               echo "Removing $sku  \n";
               $doc->documentElement->removeChild($node); 
            }
            

        }

        $doc->save($xmlRootPath . $xmlDocument);

        $index++;

    }

}