<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Setup\Operation;

use Amasty\Fpc\Api\Data\BackgroundJobInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateJobQueueTable
{
    const TABLE_NAME = 'amasty_fpc_job_queue';

    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }
    private function createTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(self::TABLE_NAME);

        return $setup->getConnection()
            ->newTable(
                $tableName
            )->setComment(
                'Amasty FPC Table with deferred jobs to process in background'
            )->addColumn(
                BackgroundJobInterface::JOB_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Job ID'
            )->addColumn(
                BackgroundJobInterface::JOB_CODE,
                Table::TYPE_TEXT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Job Code'
            );
    }
}
