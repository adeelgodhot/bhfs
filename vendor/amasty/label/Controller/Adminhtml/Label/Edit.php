<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Label;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\LabelRepositoryInterface;
use Amasty\Label\Model\LabelRegistry;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_Label::label';

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        LabelRepositoryInterface $labelRepository,
        Context $context,
        Escaper $escaper,
        LabelRegistry $labelRegistry,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->labelRepository = $labelRepository;
        $this->escaper = $escaper;

        parent::__construct($context);
        $this->labelRegistry = $labelRegistry;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($id = $this->getRequest()->getParam('id', false)) {
            try {
                $label = $this->getLabelInfo((int) $id);
                $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
                $resultPage->getConfig()->getTitle()->prepend(
                    __('Edit Label `%1`', $this->escaper->escapeHtml($label->getName()))
                );
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This label no longer exists...'));

                return $this->_redirect('*/*/index');
            }
        } else {
            $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
            $resultPage->getConfig()->getTitle()->prepend(__('Create New Label'));
        }

        return $resultPage;
    }

    private function getLabelInfo(int $id): DataObject
    {
        $persistedData = $this->_getSession()->getAmLabelPageData();
        $dbLabel = $this->labelRepository->getById($id);

        if (!empty($persistedData)) {
            $persistedData[LabelInterface::LABEL_ID] = $dbLabel->getLabelId();
            $label = $this->dataObjectFactory->create(['data' => $persistedData]);
            $this->labelRegistry->register(LabelRegistry::PERSISTED_DATA, $label);
        } else {
            $label = $dbLabel;
            $this->labelRegistry->setCurrentLabel($label);
        }

        return $label;
    }
}
