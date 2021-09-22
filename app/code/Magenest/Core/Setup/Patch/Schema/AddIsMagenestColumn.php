<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 */

namespace Magenest\Core\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Psr\Log\LoggerInterface;

class AddIsMagenestColumn implements SchemaPatchInterface
{

    const ADMIN_TABLE     = 'adminnotification_inbox';
    const MAGENEST_COLUMN = 'is_magenest';

    /** @var SchemaSetupInterface */
    protected $_schemaSetup;

    /** @var LoggerInterface */
    protected $_logger;

    /**
     * AddIndexProductIdRentalProduct constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        LoggerInterface $logger
    ) {
        $this->_schemaSetup = $schemaSetup;
        $this->_logger      = $logger;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $connection = $this->_schemaSetup->getConnection();
        $table      = $connection->getTableName(self::ADMIN_TABLE);
        if (!$connection->tableColumnExists($table, self::MAGENEST_COLUMN)) {
            try {
                $connection->addColumn(
                    $table,
                    self::MAGENEST_COLUMN,
                    [
                        'nullable' => true,
                        'type'     => Table::TYPE_SMALLINT,
                        'comment'  => 'Is Magenest',
                        'default'  => 0
                    ]
                );
            } catch (\Exception $exception) {
                $this->_logger->debug($exception->getMessage());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
