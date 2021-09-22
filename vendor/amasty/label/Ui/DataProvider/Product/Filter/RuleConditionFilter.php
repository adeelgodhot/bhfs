<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Product\Filter;

use Amasty\Label\Model\Rule;
use Amasty\Label\Model\RuleFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;
use Zend\Uri\Uri as ZendUri;

class RuleConditionFilter implements AddFilterToCollectionInterface
{
    const MATCHED_FLAG = 'matched_products';

    /**
     * @var RuleConditionFactory
     */
    private $ruleConditionFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Zend_Uri
     */
    private $zendUri;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        RuleFactory $ruleConditionFactory,
        Json $jsonSerializer,
        ZendUri $zendUri,
        StoreManagerInterface $storeManager
    ) {
        $this->ruleConditionFactory = $ruleConditionFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->zendUri = $zendUri;
        $this->storeManager = $storeManager;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param ProductCollection|Collection $collection
     * @param string $field
     * @param null $condition
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $matchedProductIds = $this->getMatchedProducts($condition['eq']);

        if ($matchedProductIds) {
            $collection->addIdFilter($matchedProductIds);
        } else {
            $collection->getSelect()->where('null');
        }

        $collection->setFlag(self::MATCHED_FLAG, $matchedProductIds);
    }

    private function getMatchedProducts(string $queryCondition): array
    {
        $conditions = $this->parseQueryToArray($queryCondition);
        /** @var Rule $ruleCondition */
        $ruleCondition = $this->ruleConditionFactory->create();
        $ruleCondition->setStores(Store::DEFAULT_STORE_ID);
        $ruleCondition->loadPost($conditions['rule'] ?? []);

        return array_keys($ruleCondition->getMatchingProductIdsByLabel());
    }

    private function parseQueryToArray(string $query): array
    {
        $this->zendUri->setQuery($query);
        return $this->zendUri->getQueryAsArray();
    }
}
