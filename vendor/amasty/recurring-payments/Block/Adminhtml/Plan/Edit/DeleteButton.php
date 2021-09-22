<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Block\Adminhtml\Plan\Edit;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        parent::__construct($context);
        $this->request = $context->getRequest();
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];

        if ($planId = $this->request->getParam(SubscriptionPlanInterface::PLAN_ID)) {
            $data = [
                'label' => __('Delete Plan'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm("' . __('Are you sure you want to do this?') . '", "'
                    . $this->getUrl('*/*/delete', [SubscriptionPlanInterface::PLAN_ID => $planId]) . '")',
                'sort_order' => 20,
            ];
        }

        return $data;
    }
}
