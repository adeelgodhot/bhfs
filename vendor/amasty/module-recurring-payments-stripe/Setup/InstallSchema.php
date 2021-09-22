<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Setup;

use Amasty\RecurringStripe\Setup\Operation\CreateProductTable;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @codingStandardsIgnoreStart
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var CreateProductTable
     */
    private $createProductTable;

    public function __construct(
        CreateProductTable $createProductTable
    ) {
        $this->createProductTable = $createProductTable;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->createProductTable->execute($installer);

        $installer->endSetup();
    }
}
