<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\DataFeedManager\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Wyomind\DataFeedManager\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var \Wyomind\Framework\Helper\ModuleFactory
     */
    public $license;

    /**
     * UpgradeSchema constructor.
     * @param \Wyomind\Framework\Helper\License\UpdateFactory $license
     */
    public function __construct(\Wyomind\Framework\Helper\License\UpdateFactory $license)
    {
        $this->license = $license;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $this->license->create()->update(__CLASS__, $context);


        if (version_compare($context->getVersion(), '11.6.0') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $installer->getConnection()->addColumn(
                $installer->getTable('datafeedmanager_feeds'),
                'ftp_ssl',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'default' => '0', 'length' => 1, "comment" => "Enable SSL"]
            );

            $installer->endSetup();
        }
    }
}