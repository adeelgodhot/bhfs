<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Label;

use Amasty\Label\Api\LabelRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Delete extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        LabelRepositoryInterface $labelRepository,
        LoggerInterface $logger
    ) {
        $this->labelRepository = $labelRepository;

        parent::__construct($context);
        $this->logger = $logger;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $this->labelRepository->deleteById((int) $id);
                $this->messageManager->addSuccessMessage(__('You deleted the label.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                return $resultRedirect->setPath('amasty_label/*/edit', ['id' =>  $id]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a item to delete.'));
        }

        return $resultRedirect->setPath('amasty_label/label/index');
    }
}
