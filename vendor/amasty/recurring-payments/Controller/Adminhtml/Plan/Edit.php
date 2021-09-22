<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Controller\Adminhtml\Plan;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Amasty\RecurringPayments\Model\SubscriptionPlan;
use Amasty\RecurringPayments\Model\SubscriptionPlanFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{
    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $repository;

    /**
     * @var SubscriptionPlanFactory
     */
    private $subscriptionPlanFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Action\Context $context,
        SubscriptionPlanRepositoryInterface $repository,
        SubscriptionPlanFactory $subscriptionPlanFactory,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $planId = $this->getRequest()->getParam(SubscriptionPlanInterface::PLAN_ID);
        if ($planId) {
            try {
                $model = $this->repository->getById($planId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Subscription Plan does not exist.'));

                return $this->_redirect('*/*/');
            }
        } else {
            /** @var SubscriptionPlan $model */
            $model = $this->subscriptionPlanFactory->create();
        }

        if ($savedData = $this->dataPersistor->get('amasty_recurring_payments_subscription_plan')) {
            $model->addData($savedData);
            $this->dataPersistor->clear('amasty_recurring_payments_subscription_plan');
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $title = $model->getName() ?
            __("Edit Subscription Plan \"%1\"", $model->getName()) :
            __('New Subscription Plan');

        $resultPage->setActiveMenu('Amasty_RecurringPayments::recurring_payments_subscription_plans');
        $resultPage->addBreadcrumb(__('Subscription Plans'), __('Subscription Plans'));
        $resultPage->addBreadcrumb($title, $title);
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
