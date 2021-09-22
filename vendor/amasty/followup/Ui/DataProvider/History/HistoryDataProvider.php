<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Ui\DataProvider\History;

use Amasty\Followup\Model\ResourceModel\History\CollectionFactory;

class HistoryDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collectionInitialized = false;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getCollection()
    {
        $collection = parent::getCollection();
        $collection->addFieldToFilter(
            'main_table.status',
            array('neq' => \Amasty\Followup\Model\History::STATUS_PENDING)
        );

        if (!$this->collectionInitialized){
            $collection->addRuleData();

            $this->collectionInitialized = true;
        }

        return $collection;
    }
}