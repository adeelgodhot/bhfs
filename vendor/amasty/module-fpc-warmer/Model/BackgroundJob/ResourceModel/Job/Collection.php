<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\BackgroundJob\ResourceModel\Job;

use Amasty\Fpc\Model\BackgroundJob\Job;
use Amasty\Fpc\Model\BackgroundJob\ResourceModel\Job as JobResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Job::class, JobResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
