<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Plugin\Framework\Search\Adapter\Mysql\Aggregation\Builder;

use Magento\Catalog\Model\Product;

class Dynamic
{
    const FROM_TO_WIDGET = '1';

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Search\Dynamic\DataProviderInterface
     */
    private $priceDataProvider;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Amasty\Shopby\Helper\FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var Collection
     */
    protected $dataProvider;

    /**
     * @var \Magento\Framework\Search\Dynamic\EntityStorageFactory
     */
    protected $entityStorageFactory;

    /**
     * @var array
     */
    private $data = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Eav\Model\Config $eavConfig,
        \Amasty\Shopby\Helper\FilterSetting $filterSettingHelper,
        \Magento\Framework\Search\Dynamic\DataProviderInterface $priceDataProvider,
        \Magento\Framework\Search\Dynamic\EntityStorageFactory $entityStorageFactory
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->eavConfig = $eavConfig;
        $this->filterSettingHelper = $filterSettingHelper;
        $this->priceDataProvider = $priceDataProvider;
        $this->entityStorageFactory = $entityStorageFactory;
    }

    /**
     * @param \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Dynamic $subject
     * @param \Closure $closure
     * @param \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface $dataProvider
     * @param array $dimensions
     * @param \Magento\Framework\Search\Request\BucketInterface $bucket
     * @param \Magento\Framework\DB\Ddl\Table $entityIdsTable
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundBuild(
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\Builder\Dynamic $subject,
        \Closure $closure,
        \Magento\Framework\Search\Adapter\Mysql\Aggregation\DataProviderInterface $dataProvider,
        array $dimensions,
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        \Magento\Framework\DB\Ddl\Table $entityIdsTable
    ) {
        $dataKey = $bucket->getName() . $bucket->getField() . $bucket->getType();
        if (!isset($this->data[$dataKey])) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $bucket->getField());

            if ($attribute->getBackendType() == 'decimal') {
                if ($attribute->getAttributeCode() == 'price') {
                    $minMaxData['data'] = $this->priceDataProvider->getAggregations(
                        $this->entityStorageFactory->create($entityIdsTable)
                    );
                    $minMaxData['data']['value'] = 'data';
                } else {
                    $currentScope = $dimensions['scope']->getValue();
                    $currentScopeId = $this->scopeResolver->getScope($currentScope)->getId();
                    $select = $this->resource->getConnection()->select();
                    $table = $this->resource->getTableName(
                        'catalog_product_index_eav_decimal'
                    );
                    $select->from(
                        ['main_table' => $table],
                        [
                            'value' => new \Zend_Db_Expr("'data'"),
                            'min' => 'min(main_table.value)',
                            'max' => 'max(main_table.value)',
                            'count' => 'count(*)'
                        ]
                    )
                        ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
                        ->where('main_table.store_id = ? ', $currentScopeId);
                    $select->joinInner(
                        ['entities' => $entityIdsTable->getName()],
                        'main_table.entity_id  = entities.entity_id',
                        []
                    );

                    $minMaxData = $dataProvider->execute($select);
                }

                $defaultData = $closure($dataProvider, $dimensions, $bucket, $entityIdsTable);

                return array_replace($minMaxData, $defaultData);
            }

            $this->data[$dataKey] = $closure($dataProvider, $dimensions, $bucket, $entityIdsTable);
        }

        return $this->data[$dataKey];
    }
}
