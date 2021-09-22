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
use Psr\Log\LoggerInterface;

class Delete extends \Amasty\Followup\Controller\Adminhtml\Rule
{
    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        SalesRuleFactory $salesRuleFactory,
        LoggerInterface $logger,
        RuleFactory $ruleFactory
    ) {
        $this->logger = $logger;
        $this->ruleFactory = $ruleFactory;

        parent::__construct(
            $context,
            $coreRegistry,
            $salesRuleFactory
        );
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->ruleFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the rule.'));
                $this->_redirect('amasty_followup/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('We can\'t delete the rule right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $this->_redirect('amasty_followup/*/edit', ['id' => $this->getRequest()->getParam('id')]);

                return;
            }
        }

        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        $this->_redirect('amasty_followup/*/');
    }
}
