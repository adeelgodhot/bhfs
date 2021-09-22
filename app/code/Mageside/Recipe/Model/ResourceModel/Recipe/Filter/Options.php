<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\ResourceModel\Recipe\Filter;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Options extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Options constructor.
     * @param Context $context
     * @param RequestInterface $request
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        $connectionName = null
    ) {
        $this->_request = $request;
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('ms_recipe_filter_options', 'id');
    }

    /**
     * Save options
     * @param $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveOptions($data)
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $data);

        return $this;
    }

    /**
     * Update options
     * @param $options
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateOptions($options)
    {
        $connection = $this->getConnection();

        foreach ($options as $option) {
            $connection->update(
                $this->getMainTable(),
                [
                    'option_image'  => !empty($option['option_image']) ? $option['option_image'] : null
                ],
                ['id = ?'   => (int)$option['id']]
            );
        }

        return $this;
    }

    /**
     * Delete options by ids
     * @param $ids
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOptions($ids)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), ['id IN (?)' => $ids]);

        return $this;
    }

    public function saveFilterOptionsData($object)
    {
        $currentOptions = $object->loadOptionsByFilterId($object->getId())->getData();
        $currentStoreId = $object->getData('$store_id');
        $connection = $this->getConnection();
        $tableName = $connection->getTableName('ms_recipe_filter_options_varchar');
        $optionsDeleteIds = [];
        if (isset($this->_request->getParam('filter')['options'])) {
            $options = $this->_request->getParam('filter')['options'];
            foreach ($options as $key => $option) {
                $optionId = $this->getConnection()->fetchOne(
                    $this->getConnection()->select()
                        ->from($tableName, ['id'])
                        ->where(
                            "meta_key = \"label\" AND
                        store_id = " . $currentStoreId . " AND
                        filter_option_id = " . $currentOptions[$key]['id']
                        )
                );
                if ($currentStoreId > 0 && !$option['label_is_default'] || $currentStoreId == 0) {
                    $prepareDate = [
                        "filter_option_id" => $currentOptions[$key]['id'],
                        "store_id" => $currentStoreId,
                        "meta_key" => "label",
                        "meta_value" => $option['label'],
                    ];

                    if ($optionId) {
                        $select = $this->getConnection()->update(
                            $tableName,
                            $prepareDate,
                            "id =" . $optionId . " AND
                    meta_key = \"label\" AND
                    store_id = " . $currentStoreId . " AND
                    filter_option_id = " . $option['id']
                        );
                    } else {
                        $select = $this->getConnection()->insert(
                            $tableName,
                            $prepareDate
                        );
                    }
                } elseif ($currentStoreId > 0 && $option['label_is_default'] && $optionId) {
                    $optionsDeleteIds[] = $optionId;
                }
            }
        }

        $select = $this->getConnection()->delete($tableName, ['id IN (?)' => $optionsDeleteIds]);
    }
}
