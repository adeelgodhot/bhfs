<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\Recipe\Block\Adminhtml\Recipe;

class Writer extends \Magento\Backend\Block\Template
{
    /**
     * Path to template file in theme.
     * @var string
     */
    protected $_template = 'Mageside_Recipe::recipe/fieldset.phtml';

    /**
     * Recipe model
     * @var \Mageside\Recipe\Model\Recipe
     */
    protected $_recipe;

    /**
     * @var
     */
    protected $_writerId;

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Writer\Collection
     */
    protected $_writer;
    /**
     * @var null 
     */
    protected $writerName = null;
    /**
     * @var bool
     */
    protected $isWriter = false;

    /**
     * Writer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Mageside\Recipe\Model\Recipe $recipe
     * @param \Magento\Framework\Registry $registry
     * @param \Mageside\Recipe\Model\ResourceModel\Writer\Collection $writer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Mageside\Recipe\Model\Recipe $recipe,
        \Magento\Framework\Registry $registry,
        \Mageside\Recipe\Model\ResourceModel\Writer\Collection $writer,
        array $data = []
    ) {
        $this->_recipe = $recipe;
        $this->_coreRegistry = $registry;
        $this->_writer = $writer;
        parent::__construct($context, $data);
    }

    /**
     * Getting URL for edit recipe
     * @return bool|string
     */
    public function getWriterUrl()
    {
        if ($this->getWriterId()) {
            $recipeUrl = $this->_urlBuilder->getUrl('customer/index/edit', ['id' => $this->getWriterId()]);

            return $recipeUrl;
        }

        return false;
    }

    public function isWriter()
    {
        $this->getWriterName();
        return $this->isWriter;
    }
    /**
     * Getting recipe name
     * @return bool|string
     */
    public function getWriterName()
    {
        if (!$this->writerName) {
            if ($this->getWriterId()) {
                $writer = $this->_writer->addFieldToFilter('customer_id', $this->getWriterId())
                    ->getWriterRecipe()
                    ->setPageSize(1)
                    ->getFirstItem();
                if ($writer->getId()) {
                    if ($writer->getIsWriter()) {
                        if ($writer->getId()) {
                            if ($writer->getNickname()) {
                                $this->writerName = $writer->getNickname();
                                $this->isWriter = true;
                                return $this->writerName;
                            } else {
                                $this->writerName = $writer->getName();
                                $this->isWriter = true;
                                return $this->writerName;
                            }
                        }
                    }
                    return $this->writerName =__('Customer is not a writer');
                }
                return $this->writerName =__('Writer is not set');
            }
        }
        return $this->writerName;
    }

    /**
     * Getting recipe ID for later using
     * @return bool|int
     */
    protected function getWriterId()
    {
        if (!$this->_writerId) {
            $this->_writerId = false;
            if ($this->_writerId = $this->getRequest()->getParam('customer_id')) {
                return $this->_writerId;
            }
            if ($recipe = $this->_coreRegistry->registry('current_recipe')) {
                if ($recipe->getCustomerId()) {
                    $this->_writerId = $recipe->getCustomerId();
                } elseif ($this->_coreRegistry->registry('new_review_data')) {
                    $this->_writerId = $this->_coreRegistry->registry('new_review_data')->getEntityPkValue();
                }
            }
        }

        return $this->_writerId;
    }
}
