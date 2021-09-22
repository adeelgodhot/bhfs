<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Store\Model\StoreManagerInterface;
use Mageside\Recipe\Helper\Config;
use Mageside\Recipe\Model\FileUploader;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @var Filter\Options
     */
    protected $_optionsResourceModel;

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var string
     */
    protected $_mainTable = 'ms_recipe_filter';

    /**
     * @var FileUploader
     */
    protected $_fileUploader;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $_helper;

    /**
     * Collection constructor.
     * @param StoreManagerInterface $storeManager
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'ms_recipe_filter'
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable);
    }

    /**
     * Init select for recipe grid collection
     * @return \Mageside\Recipe\Model\ResourceModel\Recipe\Grid\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initSelect()
    {
        $this->joinOptionData();

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function joinOptionData()
    {
        $joinFields = [
            'type' => 'varchar',
        ];

        foreach ($joinFields as $fieldName => $backendType) {
            $tableName = $this->getTable('ms_recipe_filter_' . $backendType);

            $this->getSelect()
                ->from(['main_table' => $this->getMainTable()])
                ->reset(\Zend_Db_Select::COLUMNS)
                ->joinLeft(
                    ["at_{$fieldName}" => $tableName],
                    "main_table.id = at_{$fieldName}.filter_id AND at_{$fieldName}.store_id= 0",
                    null
                )
                ->columns(
                    [
                    "id" => "main_table.id",
                    "code" => "main_table.code",
                    "filter_id" => "at_{$fieldName}.filter_id",
                    "store_id" => "at_{$fieldName}.store_id",
                    $fieldName => "at_{$fieldName}.meta_value"
                    ]
                );
        }

        return $this;
    }
}
