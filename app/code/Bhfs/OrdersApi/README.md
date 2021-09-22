# Mage2 Module Bhfs OrdersApi

    ``bhfs/module-ordersapi``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Module responsible for exporting (managing) orders to CLF/other providers 

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Bhfs`
 - Enable the module by running `php bin/magento module:enable Bhfs_OrdersApi`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require bhfs/module-ordersapi`
 - enable the module by running `php bin/magento module:enable Bhfs_OrdersApi`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Controller
	- frontend > clf_orders_export/index/index

 - Helper
	- Bhfs\OrdersApi\Helper\Data

 - Observer
	- checkout_submit_all_after > Bhfs\OrdersApi\Observer\Checkout\SubmitAllAfter


## Attributes



