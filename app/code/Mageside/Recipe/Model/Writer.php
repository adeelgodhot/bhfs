<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model;

class Writer extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var ResourceModel\Recipe\Collection
     */
    protected $_recipeCollection;

    /**
     * Writer constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param ResourceModel\Recipe\Collection $recipeCollection
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Collection $recipeCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_recipeCollection = $recipeCollection;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Mageside\Recipe\Model\ResourceModel\Writer::class);
    }
}
