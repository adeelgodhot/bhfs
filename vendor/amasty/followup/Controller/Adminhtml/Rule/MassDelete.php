<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml\Rule;

use Amasty\Followup\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Followup\Model\SalesRuleFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends \Amasty\Followup\Controller\Adminhtml\Rule
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        SalesRuleFactory $salesRuleFactory,
        LoggerInterface $logger,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;

        parent::__construct(
            $context,
            $coreRegistry,
            $salesRuleFactory
        );
    }

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            foreach ($collection as $rule) {
                $rule->delete();
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while delete rule. Please review the error log.')
            );
            $this->logger->critical($e);
        }

        $this->_redirect('amasty_followup/*/index');
    }
}
