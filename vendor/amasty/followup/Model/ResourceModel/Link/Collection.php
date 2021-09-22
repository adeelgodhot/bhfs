<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\ResourceModel\Link;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\Link', 'Amasty\Followup\Model\ResourceModel\Link');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
    
    public function addHistoryData()
    {
        $this->getSelect()->join( 
                array('history' => $this->getTable('amasty_amfollowup_history')),
                'main_table.history_id = history.history_id',
                array()
        );
        return $this;
    }
    
}
