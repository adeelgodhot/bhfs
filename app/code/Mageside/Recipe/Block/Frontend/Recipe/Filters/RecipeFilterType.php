<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Block\Frontend\Recipe\Filters;

class RecipeFilterType extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory
     */
    protected $_filterFactory;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory
     */
    protected $_optionsCollectionFactory;

    /**
     * FilterType constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterFactory
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory $optionsCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterFactory,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory $optionsCollectionFactory,
        array $data = []
    ) {
        $this->_filterFactory = $filterFactory;
        $this->_optionsCollectionFactory = $optionsCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getFilterType()
    {
        $filterCollection = $this->_filterFactory->create();
        $filterCollection = $filterCollection->joinOptionData();
        return $filterCollection;
    }

    public function getFilterOption($id)
    {
        $options = $this->_optionsCollectionFactory->create();
        $options = $options->joinOptionData($id);
        $this->setData('options', $options->getData('label'));

        return $this;
    }

    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('recipe/recipe/listView');
    }
}
