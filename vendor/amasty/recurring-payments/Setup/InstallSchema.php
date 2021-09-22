<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup;

use Amasty\RecurringPayments\Setup\Operation\CreateAddressTable;
use Amasty\RecurringPayments\Setup\Operation\CreateFeeTable;
use Amasty\RecurringPayments\Setup\Operation\CreateTransactionTable;
use Amasty\RecurringPayments\Setup\Operation\CreateDiscountTable;
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
     * @var CreateFeeTable
     */
    private $createFeeTable;

    /**
     * @var CreateTransactionTable
     */
    private $createTransactionTable;

    /**
     * @var Operation\CreateAddressTable
     */
    private $createAddressTable;

    /**
     * @var CreateDiscountTable
     */
    private $createDiscountTable;

    public function __construct(
        CreateFeeTable $createFeeTable,
        CreateTransactionTable $createTransactionTable,
        CreateAddressTable $createAddressTable,
        CreateDiscountTable $createDiscountTable
    ) {
        $this->createFeeTable = $createFeeTable;
        $this->createTransactionTable = $createTransactionTable;
        $this->createAddressTable = $createAddressTable;
        $this->createDiscountTable = $createDiscountTable;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->createFeeTable->execute($installer);
        $this->createTransactionTable->execute($installer);
        $this->createAddressTable->execute($installer);
        $this->createDiscountTable->execute($installer);

        $installer->endSetup();
    }
}
