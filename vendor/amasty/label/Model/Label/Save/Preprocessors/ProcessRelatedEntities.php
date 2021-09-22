<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\AddCustomerGroupsData;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\AddStoresData;
use Magento\Customer\Model\Group;
use Magento\Store\Model\Store;

class ProcessRelatedEntities implements DataPreprocessorInterface
{
    public function process(array $data): array
    {
        $relatedEntitiesArray = [
            AddCustomerGroupsData::DATA_SCOPE => Group::CUST_GROUP_ALL,
            AddStoresData::DATA_SCOPE => Store::DEFAULT_STORE_ID
        ];

        foreach ($relatedEntitiesArray as $dataKey => $defaultValue) {
            if (array_key_exists($dataKey, $data) && empty($data[$dataKey])) {
                $data[$dataKey] = [$defaultValue];
            }

            $data[$dataKey] = array_map('intval', (array) $data[$dataKey]);
        }

        return $data;
    }
}
