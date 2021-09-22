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
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Throwable as Throwable;

class Duplicate extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    public function __construct(
        Context $context,
        LabelRepositoryInterface $labelRepository,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->labelRepository = $labelRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            try {
                $this->labelRepository->duplicateLabel($id);
                $this->messageManager->addSuccessMessage(__('You have duplicated the label.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('We can\'t find a item to duplicate.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Throwable $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t duplicate item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a item to duplicate.'));
        }

        return $resultRedirect->setPath('amasty_label/label/index');
    }
}
