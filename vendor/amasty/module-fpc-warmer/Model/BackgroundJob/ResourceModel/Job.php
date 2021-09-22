<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\BackgroundJob\ResourceModel;

use Amasty\Fpc\Api\Data\BackgroundJobInterface;
use Amasty\Fpc\Setup\Operation\CreateJobQueueTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Job extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateJobQueueTable::TABLE_NAME, BackgroundJobInterface::JOB_ID);
    }
}
