<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Controller\Adminhtml\SalesRule;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteria;
use Magento\SalesRule\Model\Rule;

class Suggest extends Action
{
    /**
     * @const int
     */
    const PAGE_SIZE = 20;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
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
     * @return Json
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($this->getRules($this->getRequest()->getParam('label_part')));
    }

    /**
     * @param string $searchString
     *
     * @return array
     * @throws LocalizedException
     */
    private function getRules($searchString)
    {
        $result = [];
        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('name', '%' . $searchString . '%', 'like')
            ->addFilter('use_auto_generation', 1)
            ->setPageSize(self::PAGE_SIZE)
            ->create();

        $rules = $this->ruleRepository->getList($searchCriteria)->getItems();

        /** @var Rule $rule */
        foreach ($rules as $rule) {
            $result[] = [
                'label' => $rule->getName(),
                'id'    => $rule->getRuleId(),
            ];
        }

        return $result;
    }
}
