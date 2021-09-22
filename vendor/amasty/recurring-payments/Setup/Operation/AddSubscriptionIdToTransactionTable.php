<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddSubscriptionIdToTransactionTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getTable(Transaction::TABLE_NAME);

        $connection = $installer->getConnection();

        $connection->addColumn(
            $table,
            TransactionInterface::SUBSCRIPTION_ID,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 128,
                'nullable' => false,
                'comment' => 'Subscription Id',
                'default' => ''
            ]
        );
    }
}
