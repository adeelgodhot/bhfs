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

$productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
$categoryRepository = $objectManager->get('Magento\Catalog\Api\CategoryRepositoryInterface'); 


$xmldata = \simplexml_load_file("ProductStockData.xml") or die("Failed to load");

// create empty array to store our back in stock products
$newProducts = [];

$categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');

foreach($xmldata->children() as $empl) {  

    $sku = $empl->sku;
    $stock = $empl->stock;
    echo $sku . " " . $stock . "<br/>";

    $product = $productRepository->get($sku);

    if($product) {

        echo $sku . " current stock qty " . $product->getQty() . "<br/>";

        // get current stock qty
        if( $product->getQty() == 0 && $stock > 0 ){

            $product_sku = $sku;
            $product_brand = $product->getAttributeText('mgs_brand');
            $product_name = $product->getName();
            $product_price = $product->getPrice();
            $categoryIds = $product->getCategoryIds();

            $category_crumbs = "";

            if (!empty($categoryIds)) {


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

            $newProducts[] = array("sku"=>$product_sku, "brand"=>$product_brand, "name"=>$product_name, "price"=>$product_price, "category" => $category_crumbs, "stock" => $stock);  
        }

        
    }

}

if (!empty($newProducts)) {

    // echo "<pre>";
    // print_r($newProducts);
    // echo "</pre>";

    // get date
    $date = date('d/m/Y', time());

    $body = "";

    //build html body
    $body = "Here is the list of SKUs which are back in stock on $date  <br>";
    $body .= "<table>
        <thead>
            <tr>
                <th>Sku</th>
                <th>Brand</th>
                <th>Name</th>
                <th>Price</th>
                <th>Category</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>";

        foreach ($newProducts as $i => $product){

            $body .= "<tr>
                <td>" . $product['sku'] . "</td>
                <td>" . $product['brand']. "</td>
                <td>" . $product['name']. "</td>
                <td>" . number_format($product['price'], 2). "</td>
                <td>" . $product['category']. "</td>
                <td>" . $product['stock']. "</td>
            </tr>";
        }

    $body .= "</tbody>
    </table>";

    echo $body;

    $to = ['wojciech@pimdesign.com'];
    // $to = ['wojciech@pimdesign.com', 'len@besthealthfoodshop.com', 'melanie@besthealthfoodshop.com'];
    // Send Mail functionality starts from here 
    $from = "hello@besthealthfoodshop.com";
    $nameFrom = "Besthealthfoodshop Report";
    $nameTo = "";

    $email = new \Zend_Mail();
    $email->setSubject("List of SKUs which are back in stock"); 
    $email->setBodyHtml($body);     // use it to send html data
    //$email->setBodyText($body);   // use it to send simple text data
    $email->setFrom($from, $nameFrom);
    $email->addTo($to, $nameTo);
    $email->send();
}