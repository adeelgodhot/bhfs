<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Controller\Recipe;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Mageside\Recipe\Model\RecipeFactory
     */
    protected $_recipeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Mageside\Recipe\Model\RecipeFactory $recipeFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_recipeFactory = $recipeFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $recipe = $this->_recipeFactory->create();
        if ($recipeId = $this->getRequest()->getParam('recipe_id')) {
            $recipe->load($recipeId);
        }

        if ($recipe->getRecipeId()) {
            $this->_coreRegistry->register('recipe', $recipe);
        } else {
            return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }
}
