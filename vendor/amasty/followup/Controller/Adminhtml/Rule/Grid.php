<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml\Rule;

use Amasty\Followup\Model\RuleFactory;
use Amasty\Followup\Model\SalesRuleFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;

class Grid extends \Amasty\Followup\Controller\Adminhtml\Rule
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        SalesRuleFactory $salesRuleFactory,
        RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;

        parent::__construct(
            $context,
            $coreRegistry,
            $salesRuleFactory
        );
    }

    public function execute()
    {
        $ruleId = (int)$this->getRequest()->getParam('id');
        $rule = $this->ruleFactory->create();

        if ($ruleId) {
            $rule = $rule->load($ruleId);

            if (!$rule->getId()) {
                $this->messageManager->addError(__('Something went wrong while editing the rule.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('amasty_followup/*/index');

                return $resultRedirect;
            }
        }

        $this->initCurrentRule($rule);
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
