<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Controller;

/**
 * Class Router
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $_actionFactory;

    /**
     * @var \Mageside\Recipe\Model\WriterFactory
     */
    protected $_writerFactory;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Writer\CollectionFactory
     */
    protected $_writerCollectionFactory;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Collection
     */
    protected $_recipeCollectionFactory;

    /**
     * @var \Mageside\Recipe\Model\RecipeFactory
     */
    protected $_recipeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory
     */
    protected $_filterCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\Mageside\Recipe\Helper\Config
     */
    protected $_helper;

    /**
     * Router constructor.
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Mageside\Recipe\Model\WriterFactory $writerFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterCollectionFactory
     * @param \Mageside\Recipe\Helper\Config $helper
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Mageside\Recipe\Model\WriterFactory $writerFactory,
        \Mageside\Recipe\Model\ResourceModel\Writer\CollectionFactory $writerCollectionFactory,
        \Mageside\Recipe\Model\RecipeFactory $recipeFactory,
        \Mageside\Recipe\Model\ResourceModel\Recipe\CollectionFactory $recipeCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterCollectionFactory,
        \Mageside\Recipe\Helper\Config $helper
    ) {
        $this->_writerFactory = $writerFactory;
        $this->_writerCollectionFactory = $writerCollectionFactory;
        $this->_recipeCollectionFactory = $recipeCollectionFactory;
        $this->_actionFactory = $actionFactory;
        $this->_recipeFactory = $recipeFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_filterCollectionFactory = $filterCollectionFactory;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null|bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $path = trim($request->getPathInfo(), '/');
        $p = explode('/', $path);
        $route = $this->_helper->getSeoRoute();

        if (!isset($p[0]) || $p[0] != $route) {
            return null;
        }

        if (!isset($p[1])) {
            $request->setPathInfo('recipe/recipe/listview');
            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);

            return $this->_actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        }

        $writer = $this->_writerFactory->create();
        $writer->load($p[1], 'writer_url_key');

        $postfix = $this->_helper->getSeoPostfix();
        $urlKey = str_replace($postfix, '', $p[1]);
        $recipe = $this->_recipeFactory->create()->load($urlKey, 'url_key');

        if (($writer->getCustomerId() && $recipe->getRecipeId()) || (!$writer->getCustomerId() && !$recipe->getRecipeId())) {
            return null;
        }

        if (!empty($writer->getCustomerId())) {
            $request->setPathInfo('recipe/writer/view');
            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
            $request->setParam('customer_id', $writer->getCustomerId());
            return $this->_actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        }
        if (!empty($recipe->getRecipeId())) {
            $request->setPathInfo('recipe/recipe/view');
            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
            $request->setParam('recipe_id', $recipe->getRecipeId());
            return $this->_actionFactory->create(\Magento\Framework\App\Action\Forward::class);
        }

        return null;
    }
}
