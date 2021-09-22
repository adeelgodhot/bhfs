<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml;

use Amasty\Followup\Controller\RegistryConstants;
use Amasty\Followup\Model\SalesRule;
use Amasty\Followup\Model\SalesRuleFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

abstract class Rule extends \Magento\Backend\App\Action
{
    /**
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var SalesRule
     */
    protected $salesRule;

    /**
     * @var SalesRuleFactory
     */
    protected $salesRuleFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        SalesRuleFactory $salesRuleFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->salesRuleFactory = $salesRuleFactory;

        parent::__construct($context);
    }

    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Followup::followup_rule');
        $resultPage->addBreadcrumb(__('Marketing'), __('Marketing'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Followup::followup_rule');
    }

    protected function initCurrentRule($rule)
    {
        $this->coreRegistry->register(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_RULE, $rule);

        return $rule;
    }

    protected function prepareDefaultCustomerTitle(\Magento\Backend\Model\View\Result\Page $resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Rules'));
    }

    public function getSalesRule()
    {
        if (!$this->salesRule) {
            $this->salesRule = $this->salesRuleFactory
                ->create()->load($this->getId());
        }

        return $this->salesRule;
    }
}
