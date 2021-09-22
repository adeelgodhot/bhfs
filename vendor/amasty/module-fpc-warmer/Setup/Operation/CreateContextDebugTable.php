<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateContextDebugTable
{
    const TABLE_NAME = 'amasty_fpc_context_debug';

    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        return $setup->getConnection()
            ->newTable($setup->getTable(self::TABLE_NAME))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Context Debug Entity ID'
            )
            ->addColumn(
                'url',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Context Debug Entity Url'
            )
            ->addColumn(
                'context_data',
                Table::TYPE_TEXT,
                1024,
                ['nullable' => true],
                'Context Debug Entity Context Data JSON'
            )
            ->setComment('Amasty FPC Context Debug');
    }
}
