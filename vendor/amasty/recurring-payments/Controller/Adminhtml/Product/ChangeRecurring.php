<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Controller\Adminhtml\Product;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class ChangeRecurring
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ChangeRecurring extends Product
{
    /**
     * MassActions filter
     *
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Action
     */
    private $productAction;

    public function __construct(
        Context $context,
        Product\Builder $productBuilder,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Action $productAction
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productAction = $productAction;
        parent::__construct($context, $productBuilder);
    }

    /**
     * Enable product(s) for recurring payments action
     *
     * @inheritDoc
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $requestStoreId = $storeId = $this->getRequest()->getParam('store', null);
        $filterRequest = $this->getRequest()->getParam('filters', null);
        $statusOfRecurring = constant($this->getRequest()->getParam('status_of_recurring', null));

        if (null !== $storeId && null !== $filterRequest) {
            $storeId = (int)$filterRequest['store_id'] ?? 0;
        }

        try {
            $this->productAction->updateAttributes(
                $productIds,
                [
                    ProductRecurringAttributesInterface::RECURRING_ENABLE => $statusOfRecurring
                ],
                (int)$storeId
            );
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', count($productIds))
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('catalog/*/', ['store' => $requestStoreId]);
    }
}
