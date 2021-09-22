<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Controller\Adminhtml\DownloadableProduct;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\Product\Type;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;

class Suggest extends Action
{
    /**
     * @const int
     */
    const PAGE_SIZE = 20;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($this->getProducts($this->getRequest()->getParam('label_part')));
    }

    /**
     * @param string $searchString
     *
     * @return array
     * @throws LocalizedException
     */
    private function getProducts($searchString)
    {
        $result = [];
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('sku', '%' . $searchString . '%', 'like')
            ->addFilter('type_id', Type::TYPE_DOWNLOADABLE)
            ->addFilter('status', 1)
            ->setPageSize(self::PAGE_SIZE)
            ->create();

        $products = $this->productRepository->getList($searchCriteria)->getItems();

        /** @var Product $product */
        foreach ($products as $product) {
            $result[] = [
                'label' => $product->getSku() . ' - ' . $product->getName(),
                'sku'   => $product->getSku(),
                'id'    => $product->getId(),
            ];
        }

        return $result;
    }
}
