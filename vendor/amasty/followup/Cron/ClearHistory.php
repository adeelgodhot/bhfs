<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Cron;

use Amasty\Followup\Model\Source\History\ClearPeriod;
use Amasty\Followup\Model\History;

class ClearHistory
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var \Amasty\Followup\Model\ResourceModel\History\CollectionFactory
     */
    private $historyCollectionFactory;

    /**
     * @var \Amasty\Followup\Helper\Data
     */
    private $helper;

    /**
     * @var \Amasty\Followup\Model\ResourceModel\History
     */
    private $historyResource;

    public function __construct(
        \Amasty\Followup\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Amasty\Followup\Helper\Data $helper,
        \Amasty\Followup\Model\ResourceModel\History $historyResource
    ){
        $this->date = $date;
        $this->helper = $helper;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->historyResource = $historyResource;
    }

    public function execute()
    {
        $clearHistoryDays = $this->helper->getScopeValue(ClearPeriod::CLEAR_HISTORY_CONFIG_PATH);

        if ($clearHistoryDays) {
            $currentDate = $this->date->gmtDate();
            $validDate = $this->date->gmtDate('Y-m-d H:i:s', $currentDate . "-{$clearHistoryDays} days");

            /** @var \Amasty\Followup\Model\ResourceModel\History\Collection $historyCollection */
            $historyCollection = $this->historyCollectionFactory->create()
                ->addFieldToFilter('finished_at', ['lt' => $validDate])
                ->addFieldToFilter('status', History::STATUS_SENT);

            foreach ($historyCollection->getItems() as $historyEntity) {
                $this->historyResource->delete($historyEntity);
            }
        }
    }
}
