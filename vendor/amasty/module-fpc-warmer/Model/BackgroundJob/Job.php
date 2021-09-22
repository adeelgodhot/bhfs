<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\BackgroundJob;

use Amasty\Fpc\Api\Data\BackgroundJobInterface;
use Magento\Framework\Model\AbstractModel;

class Job extends AbstractModel implements BackgroundJobInterface
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Job::class);
        $this->setIdFieldName(BackgroundJobInterface::JOB_ID);
    }

    public function getJobId(): int
    {
        return (int)$this->_getData(BackgroundJobInterface::JOB_ID);
    }

    public function setJobId(int $jobId): BackgroundJobInterface
    {
        $this->setData(BackgroundJobInterface::JOB_ID, $jobId);

        return $this;
    }

    public function getJobCode(): string
    {
        return (string)$this->_getData(BackgroundJobInterface::JOB_CODE);
    }

    public function setJobCode(string $jobCode): BackgroundJobInterface
    {
        $this->setData(BackgroundJobInterface::JOB_CODE, $jobCode);

        return $this;
    }
}
