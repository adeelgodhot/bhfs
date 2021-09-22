<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Address;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateAddressTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_address'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(Address::TABLE_NAME))
            ->addColumn(
                AddressInterface::KEY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                AddressInterface::SUBSCRIPTION_ID,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Subscription Id'
            )
            ->addColumn(
                AddressInterface::KEY_COUNTRY_ID,
                Table::TYPE_TEXT,
                30,
                ['nullable' => true],
                'Country Id'
            )
            ->addColumn(
                AddressInterface::KEY_REGION_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Region Id'
            )
            ->addColumn(
                AddressInterface::KEY_REGION_CODE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Region Code'
            )
            ->addColumn(
                AddressInterface::KEY_REGION,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Region'
            )
            ->addColumn(
                AddressInterface::KEY_STREET,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Street'
            )
            ->addColumn(
                AddressInterface::KEY_STREET,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Street'
            )
            ->addColumn(
                AddressInterface::KEY_TELEPHONE,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Telephone'
            )
            ->addColumn(
                AddressInterface::KEY_POSTCODE,
                Table::TYPE_TEXT,
                20,
                ['nullable' => true],
                'Post Code'
            )
            ->addColumn(
                AddressInterface::KEY_CITY,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'City'
            )
            ->addColumn(
                AddressInterface::KEY_FIRSTNAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'First Name'
            )
            ->addColumn(
                AddressInterface::KEY_LASTNAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Last Name'
            )
            ->addColumn(
                AddressInterface::KEY_MIDDLENAME,
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Middle Name'
            )
            ->addColumn(
                AddressInterface::KEY_PREFIX,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Prefix'
            )
            ->addColumn(
                AddressInterface::KEY_SUFFIX,
                Table::TYPE_TEXT,
                40,
                ['nullable' => true],
                'Suffix'
            )
            ->addColumn(
                AddressInterface::SAME_AS_BILLING,
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Same As Billing'
            )
            ->addIndex(
                $installer->getIdxName(Address::TABLE_NAME, [AddressInterface::SUBSCRIPTION_ID]),
                [AddressInterface::SUBSCRIPTION_ID]
            )
            ->setComment('Amasty Recurring Payments Address Table');

        $installer->getConnection()->createTable($table);
    }
}
