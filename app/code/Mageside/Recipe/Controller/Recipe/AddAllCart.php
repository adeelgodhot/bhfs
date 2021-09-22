<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\Recipe\Controller\Recipe;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Visibility;

class AddAllCart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * AddAllCart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @param \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_productRepository = $productRepository;
        $this->_productCollectionFactory = $collectionFactory;
        $this->_cart = $cart;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        try {
            $recipeId = $this->getRequest()->getParam('recipe_id');
            $collectionProducts = $this->_productCollectionFactory->create();
            $storeId = $this->_storeManager->getStore()->getId();

            $products = $collectionProducts->addFieldToFilter('recipe_id', $recipeId);
            foreach ($products->getData() as $product) {
                /** @var \Magento\Catalog\Model\Product $_product */
                $_product = $this->_productRepository->getById($product['product_id'], false, $storeId);
                if ($_product && $_product->getIsSalable()
                    && $_product->getVisibility() == Visibility::VISIBILITY_BOTH
                ) {
                    $params = ['qty' => $product['qty']];
                    $this->_cart->addProduct($_product, $params);
                }
            }
            $this->_cart->save();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong while adding products to cart.'));
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData("Ok");
    }
}
