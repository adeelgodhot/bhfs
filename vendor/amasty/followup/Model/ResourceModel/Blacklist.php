<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Blacklist extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('amasty_amfollowup_blacklist', 'blacklist_id');
    }

    public function saveImportData($emails)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $emails, array("email"));
    }
}
