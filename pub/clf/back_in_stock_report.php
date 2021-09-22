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

//  Limits the maximum execution time
set_time_limit(360);

include 'sendgrid_config.php';


/**
 * @var string
 */
const CATEGORY_PATH_SEPARATOR = "|";

/**
 * @var string
 */
const CATEGORY_LEVEL_SEPARATOR = "/";

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

$product = $objectManager->get('Magento\Catalog\Model\Product');

$categoryRepository = $objectManager->get('Magento\Catalog\Api\CategoryRepositoryInterface'); 

$collectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');

$productCollection = $collectionFactory->create();
/** Apply filters here */
$productCollection->addAttributeToSelect('*');
// It will return boolean value for Yes/NO. "1" means "Yes" and "0" means "No"

$productCollection->addFieldToFilter('previously_in_stock', 0);
$productCollection->joinField('stock_item', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', 'qty>0');
// Don't have to do this
// $productCollection->load();

$backInStockList = array();

foreach ($productCollection as $product){

    // echo 'Name  =  ' . $product->getName() . '<br>';
    $product_id =  $product->getId();
    $product_sku = $product->getSku();
    $product_brand = $product->getAttributeText('mgs_brand');
    $product_name = $product->getName();
    $product_price = $product->getPrice();
    $categoryIds = $product->getCategoryIds();

    $category_crumbs = "";

    if (!empty($categoryIds)) {

        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

        $categories = $categoryFactory->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $categoryIds)->setOrder('position', 'ASC');

        $lastCategoryElement = end($categoryIds);

        // echo $lastCategoryElement . "<br>";

        foreach ($categories as $category){

            // Shop/Beauty &amp; Toiletries/Body/Body Moisturisers

            if($path = $category->getPath()) {
                $pathIds = explode('/', $path);
                $lastPathElement = end($pathIds);
                 // start from shop
                array_shift($pathIds);
                
                foreach ($pathIds as $path){
                    $crumbs = $categoryRepository->get($path);
                   
                    // echo $crumbs->getName();
                    $category_crumbs .= $crumbs->getName();
                    // stop last /
                    if ($lastPathElement != $path) {
                        // echo "/";
                        $category_crumbs .= CATEGORY_LEVEL_SEPARATOR;
                    }
                    
                }
            }

            $category_crumbs .= CATEGORY_PATH_SEPARATOR;

        }

    // remove last seperator
    $category_crumbs = substr($category_crumbs, 0, -1);
    // echo $category_crumbs . "<br>";


    }


    // build array

    $backInStockList[] = array("sku"=>$product_sku, "brand"=>$product_brand, "name"=>$product_name, "price"=>$product_price, "category" => $category_crumbs);

    // we need to update previously_in_stock status
    $product->setPreviouslyInStock(1); // name of your custom attribute
    $product->save();
    
}


if (!empty($backInStockList)) {


    // echo "<pre>";
    // print_r($backInStockList);
    // echo "</pre>";

    $date = date('d/m/Y', time());

    $body = "";

    //build html body
    $body = "Here is the list of SKUs which are now back in stock on $date  <br>";
    $body .= "<table>
        <thead>
            <tr>
                <th>Sku</th>
                <th>Brand</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>";

        foreach ($backInStockList as $i => $product){

            $body .= "<tr>
                <td>" . $product['sku'] . "</td>
                <td>" . $product['brand']. "</td>
                <td>" . $product['name']. "</td>
                <td>" . number_format($product['price'], 2). "</td>
                <td>" . $product['category']. "</td>
            </tr>";
        }

    $body .= "</tbody>
    </table>";

    // echo $body;

    // $to = ['wojciech@pimdesign.com'];
    $to = ['wojciech@pimdesign.com', 'len@besthealthfoodshop.com', 'melanie@besthealthfoodshop.com'];
    // Send Mail functionality starts from here 
    $from = "hello@besthealthfoodshop.com";
    $nameFrom = "Besthealthfoodshop Report";
    $nameTo = "";

    $email = new \Zend_Mail();
    $transport = new \Zend_Mail_Transport_Smtp($smtpServer, $config);
    
    $email->setSubject("List of SKUs back in stock provided by CLF"); 
    $email->setBodyHtml($body);     // use it to send html data
    //$email->setBodyText($body);   // use it to send simple text data
    $email->setFrom($from, $nameFrom);
    $email->addTo($to, $nameTo);
    $email->send($transport);

}

// echo "<br> - the end - ";
