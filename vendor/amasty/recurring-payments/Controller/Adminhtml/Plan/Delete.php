<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Controller\Adminhtml\Plan;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Amasty_RecurringPayments::recurring_payments_subscription_plans';

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $repository;

    public function __construct(
        Action\Context $context,
        SubscriptionPlanRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($planId = $this->getRequest()->getParam(SubscriptionPlanInterface::PLAN_ID)) {
            try {
                $this->repository->deleteById($planId);
                $this->messageManager->addSuccessMessage(__('Subscription Plan has been deleted.'));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Subscription Plan does not exist.'));
            }
        }

        return $this->_redirect('*/*/');
    }
}
