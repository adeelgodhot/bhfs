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
use Psr\Log\LoggerInterface;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Amasty_RecurringPayments::recurring_payments_subscription_plans';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SubscriptionPlanFactory
     */
    private $subscriptionPlanFactory;

    public function __construct(
        Action\Context $context,
        SubscriptionPlanRepositoryInterface $repository,
        SubscriptionPlanFactory $subscriptionPlanFactory,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
        $this->logger = $logger;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
    }

    /**
     * Save Action
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            /** @var SubscriptionPlan $model */
            $model = $this->subscriptionPlanFactory->create();
            $planId = $this->getRequest()->getParam(SubscriptionPlanInterface::PLAN_ID);

            try {
                if ($planId) {
                    $model = $this->repository->getById($planId);
                } else {
                    $planId = null;
                }

                $model->addData($data);
                $model->setPlanId($planId);

                $this->repository->save($model);

                $this->messageManager->addSuccessMessage(__('The Subscription Plan has been saved.'));

                if ($this->getRequest()->getParam('back')) {
                    $this->redirectToEdit($model->getId());

                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->redirectToEdit($planId, $data);

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The Subscription Plan has not been saved. Please review the error log for the details.')
                );
                $this->logger->critical($e);
                $this->redirectToEdit($planId, $data);

                return;
            }
        }

        $this->_redirect('*/*/');
    }

    /**
     * Redirect to Edit or New and save $data to session
     *
     * @param null|int $planId
     * @param null|array $data
     */
    private function redirectToEdit($planId = null, $data = null)
    {
        if ($data) {
            $this->dataPersistor->set('amasty_recurring_payments_subscription_plan', $data);
        }
        if ($planId) {
            $this->_redirect('*/*/edit', [SubscriptionPlanInterface::PLAN_ID => $planId]);
            return;
        }
        $this->_redirect('*/*/new');
    }
}
