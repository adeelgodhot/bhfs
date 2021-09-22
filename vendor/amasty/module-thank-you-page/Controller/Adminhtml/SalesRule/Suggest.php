<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Controller\Adminhtml\SalesRule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;

class Suggest extends Action
{

    /**
     * @const int
     */
    const PAGE_SIZE = 20;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RuleRepositoryInterface $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Category list suggestion based on already entered symbols
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($this->getRules($this->getRequest()->getParam('label_part')));
    }

    /**
     * @param string $searchString
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getRules($searchString)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('name', '%' . $searchString . '%', 'like')
            ->addFilter('use_auto_generation', 1)
            ->addFilter('is_active', 1)
            ->setPageSize(self::PAGE_SIZE)
            ->create();

        $rules = $this->ruleRepository->getList($searchCriteria);

        $result = [];

        /** @var \Magento\SalesRule\Api\Data\RuleInterface $rule */
        foreach ($rules->getItems() as $rule) {
            $result[] = [
                'label' => $rule->getName(),
                'id'    => $rule->getRuleId(),
            ];
        }

        return $result;
    }
}
