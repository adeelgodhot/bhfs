Magento 2 Recipe by Mageside
============================

####Support
    v1.1.2 - Magento 2.1.* - Magento 2.2.* - Magento 2.3.*

####Change list
    v1.1.1 - Added Pinterest share button
    v1.1.0 - Ability to change writter for recipe. Print recipe functionality. Full support of translation from admin panel. 
    v1.0.20 - Fix for saving recipes for "allStores"
    v1.0.19 - Fixed issue deleting filter options
    v1.0.18 - Fix issue with unic "writer_url_key", added compatibility with Magento 2.3
    v1.0.17 - Review logic improvements
    v1.0.16 - Added translation file en_US
    v1.0.15 - Search logic improvements
    v1.0.14 - Added "desc" sorting for recipes list, added fix for description field
    v1.0.13 - Added update for urlKeys and required fields
    v1.0.12 - Fixed for multistore
    v1.0.11 - Images processing fixes
    v1.0.10 - Rich snippets improvements
    v1.0.8 - Fixed review availability and cooking time for Magento 2.2.5
    v1.0.6 - Fixed bug filtering recipies
    v1.0.4 - Fixed installer (added getTableName method for tables)
    v1.0.2 - Corrected product page link
    v1.0.0 - Start project

####Installation
    1. Download the archive.
    2. Make sure to create the directory structure in your Magento - 'Magento_Root/app/code/Mageside/Recipe'.
    3. Unzip the content of archive (use command 'unzip ArchiveName.zip') 
       to directory 'Magento_Root/app/code/Mageside/Recipe'.
    4. Run the command 'php bin/magento module:enable Mageside_Recipe' in Magento root.
       If you need to clear static content use 'php bin/magento module:enable --clear-static-content Mageside_Recipe'.
    5. Run the command 'php bin/magento setup:upgrade' in Magento root.
    6. Run the command 'php bin/magento setup:di:compile' if you have a single website and store, 
       or 'php bin/magento setup:di:compile-multi-tenant' if you have multiple ones.
    7. Clear cache: 'php bin/magento cache:clean', 'php bin/magento cache:flush'
