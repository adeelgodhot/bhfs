<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Controller\Recipe;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Mageside\Recipe\Model\FileUploader;
use Mageside\Recipe\Model\RecipeFactory;

/**
 * Class PrintView
 * @package Mageside\Recipe\Controller\Recipe
 */
class PrintView extends Action
{
    /** @var FileUploader */
    protected $fileUploader;

    /** @var RecipeFactory */
    protected $_recipeFactory;

    /**  @var Registry */
    protected $_coreRegistry;

    /**
     * PrintView constructor.
     * @param Context $context
     * @param RecipeFactory $recipeFactory
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        RecipeFactory $recipeFactory,
        Registry $coreRegistry
    ) {
        $this->_recipeFactory = $recipeFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $recipe = $this->_recipeFactory->create();
        if ($recipeId = $this->getRequest()->getParam('id')) {
            $recipe->load($recipeId);
        }

        if ($recipe->getRecipeId()) {
            $this->_coreRegistry->register('recipe', $recipe);
        } else {
            return $this->resultFactory->create(ResultFactory::TYPE_FORWARD)->forward('noroute');
        }

        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $imagePath = $this->fileUploader->getBaseUrl() . $this->fileUploader->getBasePath();

        return $imagePath;
    }
}
