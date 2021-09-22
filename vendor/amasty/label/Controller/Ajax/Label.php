<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Ajax;

use Amasty\Label\Model\AbstractLabels;
use Amasty\Label\Model\LabelViewer;
use Amasty\Label\Model\ResourceModel\Label\Collection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\DesignLoader;
use Psr\Log\LoggerInterface;

class Label implements HttpPostActionInterface
{
    /**
     * @var LabelViewer
     */
    private $labelViewer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ProductCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        RequestInterface $request,
        LabelViewer $labelViewer,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        DesignLoader $designLoader,
        ProductCollectionFactory $collectionFactory
    ) {
        $this->labelViewer = $labelViewer;
        $this->logger = $logger;
        $this->request = $request;
        $this->designLoader = $designLoader;
        $this->resultFactory = $resultFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $this->designLoader->load();
        $result = [];

        /** @var Product $product **/
        foreach ($this->getProductCollection() as $product) {
            $result[$product->getId()] = $this->labelViewer->renderProductLabel(
                $product,
                $this->getMode()
            );
        }

        $result = empty($result) ? [] : ['labels' => $result];
        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    private function getProductCollection(): ProductCollection
    {
        $collection = $this->collectionFactory->create();
        $collection
            ->addPriceData()
            ->addAttributeToSelect('*')
            ->addIdFilter($this->getProductIds());

        return $collection;
    }

    /**
     * @return int[]
     */
    private function getProductIds(): array
    {
        $productIds = (array) $this->request->getParam('product_ids', []);

        return array_map('intval', $productIds);
    }

    private function getMode(): int
    {
        return (bool) $this->request->getParam('in_product_list', false)
            ? Collection::MODE_LIST
            : Collection::MODE_PDP;
    }
}
