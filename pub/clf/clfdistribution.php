<?php

/**This script will generate clf data for M2 Mass Product Import & Update
	* Detect type of data we want to get:
	* 1.	Get product codes
	* 2.	Get products
	* 3.	Get products stock information
	* 4.	Get products extended data
	* 5.	Get products attributes info
*/

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// include clf class
include 'clf.php';

$live_mode = "LIVE"; // enter LIVE to use with live envarioment 

// create object
$clf_data = new ClfDistribution($live_mode);

switch ($_GET["action"]) {
    case 1:
        // echo "Get product codes";

		$xml = $clf_data->clfGetData("GetProductCodes");

		// echo $xml;
		// write to file 
		file_put_contents('ProductCodesData.xml', $xml);

        break;
    case 2:
        // echo "Get products";

		$xml = $clf_data->clfGetData("GetProductData");

		// echo $xml;
		// write to file 
		file_put_contents('ProductData.xml', $xml);

        break;
    case 3:
        // echo "Get products stock information";

		$xml = $clf_data->clfGetData("GetProductStock");

		// echo $xml;
		// write to file 
		file_put_contents('ProductStockData.xml', $xml);

        break;
    case 4:
        // echo "Get products extended data";

		$xml = $clf_data->clfGetData("GetProductExtendedData");

		// echo $xml;
		// write to file 
		file_put_contents('ProductExtendedData.xml', $xml);

        break;
    case 5:
        // echo "Get products attributes info";

		$xml = $clf_data->clfGetData("GetProductAttributes");

		// echo $xml;
		// write to file 
		file_put_contents('ProductAttributesData.xml', $xml);

        break;
}


?>