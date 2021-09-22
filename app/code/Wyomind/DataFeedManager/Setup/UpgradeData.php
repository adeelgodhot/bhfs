<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package Wyomind\DataFeedManager\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var null|\Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory
     */
    private $_feedsCollectionFactory=null;
    /**
     * @var null|\Wyomind\DataFeedManager\Model\ResourceModel\Variables\CollectionFactory
     */
    private $_variablesCollectionFactory=null;
    /**
     * @var null|\Wyomind\DataFeedManager\Model\ResourceModel\Functions\CollectionFactory
     */
    private $_functionsCollectionFactory=null;
    /**
     * @var \Magento\Framework\App\State|null
     */
    private $_state=null;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|null
     */
    private $_coreDate=null;
    /**
     * @var int
     */
    private $_storeId=1;

    /**
     * UpgradeData constructor.
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory
     * @param \Wyomind\DataFeedManager\Model\ResourceModel\Variables\CollectionFactory $variablesCollectionFactory
     * @param \Wyomind\DataFeedManager\Model\ResourceModel\Functions\CollectionFactory $functionsCollectionFactory
     * @param \Magento\Framework\App\State $state
     * @param \Wyomind\DataFeedManager\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Wyomind\DataFeedManager\Model\ResourceModel\Feeds\CollectionFactory $feedsCollectionFactory,
        \Wyomind\DataFeedManager\Model\ResourceModel\Variables\CollectionFactory $variablesCollectionFactory,
        \Wyomind\DataFeedManager\Model\ResourceModel\Functions\CollectionFactory $functionsCollectionFactory,
        \Magento\Framework\App\State $state,
        \Wyomind\DataFeedManager\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    ) {
        $this->_coreDate=$coreDate;
        $this->_feedsCollectionFactory=$feedsCollectionFactory;
        $this->_variablesCollectionFactory=$variablesCollectionFactory;
        $this->_functionsCollectionFactory=$functionsCollectionFactory;
        $this->_state=$state;
        $this->_storeId=$storeCollectionFactory->create()->getFirstStoreId();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * upgrade to 9.0.0
         */
//        if (version_compare($context->getVersion(), '9.0.0') < 0) {
//            try {
//                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
//            } catch (\Exception $e) {
//            }
//            foreach ($this->_feedsCollection as $feed) {
//                $pattern = str_replace(["'{{", "}}'",'"{{', '}}"', "php="], ["{{", "}}","{{", "}}", "output="], $feed->getProductPattern());
//                $feed->setProductPattern($pattern);
//                $feed->save();
//            }
//        }
        /**
         * upgrade to 9.0.1
         * $myPattern = null; becomes $this->skip = true;
         */
        if (version_compare($context->getVersion(), '9.0.1') < 0) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }
            $re='/\$myPattern\s*=\s*null;/';
            foreach ($this->_feedsCollectionFactory as $feed) {
                $pattern=$feed->getProductPattern();
                preg_match_all($re, $pattern, $matches);
                foreach ($matches[0] as $match) {
                    $pattern=str_replace($match, '$this->skip();', $pattern);
                }
                $feed->setSimplegoogleshoppingXmlitempattern($pattern);
                $feed->save();
            }
            $variables=$this->_variablesCollectionFactory->create();
            foreach ($variables as $variable) {
                $script=$variable->getScript();
                preg_match_all($re, $script, $matches);
                foreach ($matches[0] as $match) {
                    $script=str_replace($match, '$this->skip();', $script);
                }
                $variable->getScript($script);
                $variable->save();
            }
            $functions=$this->_functionsCollectionFactory->create();
            foreach ($functions as $function) {
                $script=$function->getScript();
                preg_match_all($re, $script, $matches);
                foreach ($matches[0] as $match) {
                    $script=str_replace($match, '$this->skip();', $script);
                }
                $function->getScript($script);
                $function->save();
            }
        }

        if (version_compare($context->getVersion(), '10.0.0') < 0) {
            //.categories index=
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }
            $re='/.categories([^|}]+)index="?\'?([0-9]+)"?\'?/';
            $feeds=$this->_feedsCollectionFactory->create();
            foreach ($feeds as $feed) {
                $pattern=$feed->getProductPattern();
                $pattern=preg_replace($re, '.categories${1}nth="${2}"', $pattern);
                $feed->setProductPattern($pattern);
                $feed->save();
            }
        }

        /**
         * upgrade to 11.1.0
         * custom functions => dfm_* to wyomind_*
         */
        if (version_compare($context->getVersion(), '11.1.0') < 0) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }
            $toReplace=["dfm_strtoupper", "dfm_strtolower", "dfm_implode", "dfm_html_entity_decode", "dfm_strip_tags", "dfm_htmlentities", "dfm_substr"];
            $replacement=["wyomind_strtoupper", "wyomind_strtolower", "wyomind_implode", "wyomind_html_entity_decode", "wyomind_strip_tags", "wyomind_htmlentities", "wyomind_substr"];

            $functions=$this->_functionsCollectionFactory->create();
            foreach ($functions as $function) {
                $function->setScript(str_replace($toReplace, $replacement, $function->getScript()));
                $function->save();
            }

            $feeds=$this->_feedsCollectionFactory->create();
            foreach ($feeds as $feed) {
                $pattern=$feed->getProductPattern();
                $pattern=str_replace($toReplace, $replacement, $pattern);
                $feed->setProductPattern($pattern);
                $feed->save();
            }
        }

        /**
         * upgrade to 11.4.0
         * add siroop template
         */
        if (version_compare($context->getVersion(), '11.4.0') < 0) {
            $data=[
                "id"=>null,
                "name"=>"Siroop",
                "type"=>1,
                "path"=>"/feeds/",
                "status"=>0,
                "updated_at"=>$this->_coreDate->date('Y-m-d H:i:s'),
                "store_id"=>$this->_storeId,
                "product_pattern"=>"<item>
<g:id>{{product.sku}}</g:id>
<g:title>{{product.name}}</g:title>
<s:long_description>{{product.description}}</s:long_description>
<s:siroop_category>{{product.siroop_category}}</s:siroop_category>
<g:image_link>{{parent.image_link index=\"0\"| product.image_link index=\"0\"}}</g:image_link>
<g:additional_image_link>{{parent.image_link index=\"1\" | product.image_link index=\"1\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"2\" | product.image_link index=\"2\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"3\" | product.image_link index=\"3\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"4\" | product.image_link index=\"4\"}}</g:additional_image_link>
<g:additional_image_link>{{parent.image_link index=\"5\" | product.image_link index=\"5\"}}</g:additional_image_link>
<g:additional_image_link/>
<!-- Availability & Price -->
<s:quantity>{{product.qty}}</s:quantity>
<g:price>{{product.price suffix=\" CHF\"}}</g:price>
<s:productvat>1</s:productvat>
<s:warranty>24</s:warranty>
<!-- Unique Product Identifiers-->
<g:gtin>{{product.ean}}</g:gtin>
<g:mpn>{{product.mpn}}</g:mpn>
<s:manufacturer_name>{{producT.manufacturer}}</s:manufacturer_name>
<g:brand>{{product.manufacturer}}</g:brand>
<!-- Products Attributes -->
<s:attribute name=\"Zusatzinformationen\">{{product.short_description}}</s:attribute>
<s:attribute name=\"Grösse\">{{product.size}}</s:attribute>
<s:attribute name=\"Farbe DE\">{{product.color}}</s:attribute>
<s:attribute name=\"Breite\">{{product.width}}</s:attribute>
<s:attribute name=\"Höhe\">{{product.height}}</s:attribute>
<s:attribute name=\"Tiefe\">{{product.depth}}</s:attribute>
<s:attribute name=\"Material\">{{product.material}}</s:attribute>
<s:attribute name=\"Volumen\">{{product.volume}}</s:attribute>
<s:attribute name=\"Geschlecht\">{{product.gender}}</s:attribute>
<s:attribute name=\"Lieferumfang\">{{product.delivery_contents}}</s:attribute>
</item>",
                "category_filter"=>1,
                "categories"=>"*",
                "type_ids"=>"*",
                "category_type"=>0,
                "visibilities"=>"*",
                "attribute_sets"=>"*",
                "attributes"=>"[]",
                "cron_expr"=>'{"days":["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],"hours":["04:00"]}',
                "include_header"=>0,
                "header"=>"<?xml version=\"1.0\" encoding=\"utf-8\"?>
<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\" 
xmlns:s=\"https://merchants.siroop.ch/\">
<channel>
<title>Products for Siroop Marketplace</title>
<link>https://www.example-shop.ch</link>;
<description>This is a sample feed containing the required and recommended attributes for a variety of different products</description>",
                "footer"=>"</channel>
</rss>",
                "encoding"=>"UTF-8",
                "enclose_data"=>1,
                "clean_data"=>1,
                "dateformat"=>"{f}",
                "ftp_enabled"=>0,
                "use_sftp"=>0,
                "ftp_active"=>0
            ];

            $setup->getConnection()->insert($setup->getTable("datafeedmanager_feeds"), $data);
        }

        if (version_compare($context->getVersion(), '11.4.1') < 0) {
            try {
                $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            } catch (\Exception $e) {

            }

            $functions=$this->_functionsCollectionFactory->create();


            foreach ($functions as $function) {
                $searchQuery="<?php if (!function_exists(";
                if (substr($function->getScript(), 0, strlen($searchQuery)) !== $searchQuery) {
                    $function->setScript(preg_replace("/<\?php\sfunction\s([a-zA-z0-9]+)/", '<?php if (!function_exists("\1")) { function \1', $function->getScript()));
                    $function->setScript(str_replace("?>", "}\n?>", $function->getScript()));
                    $function->save();
                }
            }
        }

        $setup->endSetup();
    }
}