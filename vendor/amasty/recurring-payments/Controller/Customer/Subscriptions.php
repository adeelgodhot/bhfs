<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Controller\Customer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Subscriptions
 */
class Subscriptions extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession
    ) {
        parent::__construct($context);

        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $customerId = $this->getCustomerId();

        if ($customerId) {
            $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->prepend(__('My Subscriptions'));
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } else {
            return $this->_redirect('customer/account/login');
        }
    }

    /**
     * Retrieve customer data object
     *
     * @return int
     */
    protected function getCustomerId()
    {
        return $this->customerSession->getCustomerId();
    }
}
