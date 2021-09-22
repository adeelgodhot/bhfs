<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Model\Queue\ResourceModel;

use Amasty\ImageOptimizer\Model\Queue\Queue as QueueModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Queue extends AbstractDb
{
    const TABLE_NAME = 'amasty_page_speed_optimizer_queue';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, QueueModel::QUEUE_ID);
    }

    public function clear(): void
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }

    public function deleteByIds(array $ids = []): void
    {
        $this->getConnection()->delete($this->getMainTable(), [QueueModel::QUEUE_ID . ' in (?) ' => $ids]);
    }
}
