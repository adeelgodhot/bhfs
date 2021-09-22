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

include 'sendgrid_config.php';

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

$body .= "</tbody>
</table>";

echo $body;

$transport = new \Zend_Mail_Transport_Smtp($smtpServer, $config);

$to = ['wojciech@pimdesign.com'];
// $to = ['wojciech@pimdesign.com', 'len@besthealthfoodshop.com', 'melanie@besthealthfoodshop.com'];
// Send Mail functionality starts from here 
$from = "hello@besthealthfoodshop.com";
$nameFrom = "Besthealthfoodshop Report";
$nameTo = "";

$email = new \Zend_Mail();
$email->setSubject("Test email config"); 
$email->setBodyHtml($body);     // use it to send html data
//$email->setBodyText($body);   // use it to send simple text data
$email->setFrom($from, $nameFrom);
$email->addTo($to, $nameTo);
$email->send($transport);
