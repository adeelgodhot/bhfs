<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Ui\DataProvider\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Mageside\Recipe\Model\FileUploader;
use Magento\Framework\Stdlib\ArrayManager;
use Mageside\Recipe\Model\ResourceModel\Recipe\Collection;
use Magento\Framework\Registry;
use Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Collection as FilterCollection;
use Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory;
use Mageside\Recipe\Model\RecipeFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class RecipeDataProvider extends AbstractDataProvider
{

    protected $availableImages = ['thumbnail', 'media_type_image'];

    /**
     * @var PoolInterface
     */
    protected $_pool;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var FileUploader
     */
    protected $_fileUploader;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Collection
     */
    protected $_filter;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory
     */
    protected $_filterOptionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var null|\Mageside\Recipe\Model\Recipe
     */
    private $formRecipe;

    /** @var \Magento\Framework\Registry  */
    public $registry;
    /**
     * @var RecipeFactory
     */
    private $recipeFactory;

    /**
     * RecipeDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param RecipeFactory $recipeFactory
     * @param PoolInterface $pool
     * @param RequestInterface $request
     * @param FileUploader $fileUploader
     * @param Registry $registry
     * @param FilterCollection $filter
     * @param CollectionFactory $filterOptionFactory
     * @param StoreManagerInterface $storeManager
     * @param ArrayManager $arrayManager
     * @param array $meta
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        \Mageside\Recipe\Model\RecipeFactory $recipeFactory,
        PoolInterface $pool,
        RequestInterface $request,
        FileUploader $fileUploader,
        Registry $registry,
        FilterCollection $filter,
        CollectionFactory $filterOptionFactory,
        StoreManagerInterface $storeManager,
        ArrayManager $arrayManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $this->_pool = $pool;
        $this->recipeFactory = $recipeFactory;
        $this->_request = $request;
        $this->_fileUploader = $fileUploader;
        $this->registry = $registry;
        $this->_filter = $filter;
        $this->_filterOptionFactory = $filterOptionFactory;
        $this->storeManager = $storeManager;
        $this->arrayManager = $arrayManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        $meta = $this->meta;
        $fields = [];
        $dataFilter = $this->_filter;
        $dataFilter = $dataFilter->joinOptionData();
        $dataFilter = $dataFilter->getItems();
        foreach ($dataFilter as $filter) {
            $fields[$filter->getCode()] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label'         => $filter->getType(),
                            'componentType' => 'field',
                            'options'       => $this->getFilterOptions($filter->getId()),
                            'component'     => 'Mageside_Recipe/js/components/form/multiselect',
                            'formElement'   => 'multiselect',
                            'dataScope'     => 'data.recipe.' . $filter->getCode()
                        ]
                    ]
                ]
            ];
        }

        $meta = array_replace_recursive(
            $meta,
            [
                'recipe_form' => [
                    'children' => $fields,
                ]
            ]
        );

        /** @var ModifierInterface $modifier */
        foreach ($this->_pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        $formRecipe = $this->recipeFactory->create();
        $formRecipe->load($this->_request->getParam('recipe_id'));
        $data = $formRecipe->getData();
        $dataAll=[];

        $joinFields = [
            'title' => ['recipe_form','input',"field"],
            'short_description' => ['recipe_form','wysiwyg',"field"],
        ];
        foreach ($joinFields as $fieldName => $fieldData) {
            if ((isset($data[$fieldName.'_is_default'])) && ($this->_request->getParam('store'))) {
                $useDefaultConfig = [
                    'formElement' => $fieldData[1],
                    'componentType' => $fieldData[2],
                    'usedDefault' =>  $data[$fieldName.'_default_value'] ? true : false,
                    'disabled'    => $data[$fieldName.'_is_default'] ? true : false,
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ]
                ];
                $meta[$fieldData[0]]['children'][$fieldName]['arguments']['data']['config'] = $useDefaultConfig;
            }
        }

        $cookingFields = [
            'ingredients'   => ['measure', 'ingredient', 'url', 'actionDelete'],
            'method'        => ['step', 'actionDelete']
        ];
        foreach ($cookingFields as $container => $fields) {
            if ((isset($data[$container.'_is_default'])) && ($this->_request->getParam('store'))) {
                $useDefaultConfig = [
                    'use_default'   => $data[$container . '_default_value'] ? true : false,
                    'usedDefault'   => $data[$container . '_default_value'] ? true : false,
                    'disabled'      => $data[$container . '_is_default'] ? true : false,
                    'uid'           => $container,
                    'service'       => [
                        'template'  => 'ui/form/element/helper/service',
                    ],
                ];
                $meta["cooking"]["children"][$container]["arguments"]["data"]["config"] = $useDefaultConfig;
                foreach ($fields as $field) {
                    $meta["cooking"]["children"][$container]
                    ["children"]["option_" . $container]
                    ["children"][$field]["arguments"]
                    ["data"]["config"]
                    ["imports"]["disabled"] = '${$.namespace}.${$.namespace}.cooking.' . $container . ':isUseDefault';
                }
            }
        }

        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $recipe = $this->collection->getFirstItem();
        if ($recipe && $recipe->getId()) {
            $this->data[$recipe->getId()]['recipe'] = $this->prepareData($recipe);
        }

        $customerId = $this->_request->getParam('customer_id');
        if ($customerId) {
            if ($recipe->getId()) {
                $this->data[$recipe->getId()]['recipe']['customer_id'] = $customerId;
            } else {
                $this->data['config']['data']['recipe']['customer_id'] = $customerId;
            }
        }

        /** @var ModifierInterface $modifier */
        foreach ($this->_pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data);
        }

        return $this->data;
    }

    /**
     * @param $filterItem
     * @return mixed
     */
    protected function prepareData($filterItem)
    {
        $prepareOption = $filterItem->toArray();
        foreach ($this->availableImages as $imageName) {
            if (!empty($prepareOption[$imageName])) {
                $imageData = [
                    'name' => $prepareOption[$imageName],
                    'url' =>  $this->_fileUploader->getFileWebUrl($prepareOption[$imageName])
                ];
                $prepareOption[$imageName] = [];
                $prepareOption[$imageName][0] = $imageData;
            }
        }

        if (!empty($prepareOption['options'])) {
            foreach ($prepareOption['options'] as $filterName => $filterData) {
                foreach ($filterData as $filterOptionId) {
                    $prepareOption[$filterName][] = $filterOptionId['option_id'];
                }
            }
        }

        return $prepareOption;
    }

    /**
     * @param $filterId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFilterOptions($filterId)
    {
        $filterCollection = $this->_filterOptionFactory->create();
        $filterCollection = $filterCollection->joinOptionData($filterId)->getItems();
        $option = [];
        foreach ($filterCollection as $filterOption) {
            $option[] = [
                'label' => $filterOption->getLabel(),
                'value' => $filterOption->getId()
            ];
        }

        return $option;
    }
}
