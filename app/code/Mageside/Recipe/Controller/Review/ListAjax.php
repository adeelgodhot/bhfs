<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Controller\Review;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class ListAjax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Mageside\Recipe\Model\RecipeFactory
     */
    protected $_recipeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * ListAjax constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageside\Recipe\Model\RecipeFactory $recipeFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageside\Recipe\Model\RecipeFactory $recipeFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_recipeFactory = $recipeFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     * @throws LocalizedException
     */
    public function execute()
    {
        if (!$this->initRecipe()) {
            throw new LocalizedException(__('Cannot initialize recipe.'));
        } else {
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        }

        return $resultLayout;
    }

    /**
     * @return \Mageside\Recipe\Model\Recipe|bool
     */
    protected function initRecipe()
    {
        if (!$recipeId = (int)$this->getRequest()->getParam('id')) {
            return false;
        }

        $recipe = $this->_recipeFactory->create()->load($recipeId);
        if ($recipe->getId()) {
            $this->_coreRegistry->register('recipe', $recipe);
            return $recipe;
        }

        return false;
    }
}
