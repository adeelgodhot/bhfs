<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Rewards
 */


namespace Amasty\Rewards\Ui\Component\Listing\Column;

use Amasty\Rewards\Model\Repository\RewardsRepository;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;

class PointsColumn extends \Magento\Customer\Ui\Component\Listing\Columns
{

    const FIELD_NAME = 'amount';

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var RewardsRepository
     */
    private $rewardsRepository;

    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        AttributeRepository $attributeRepository,
        InlineEditUpdater $inlineEditor,
        CustomerRepository $customerRepository,
        RewardsRepository $rewardsRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $columnFactory, $attributeRepository, $inlineEditor, $components, $data);
        $this->customerRepository = $customerRepository;
        $this->rewardsRepository = $rewardsRepository;
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var \Amasty\Rewards\Model\Rewards $reward */
                $rewardBalance = $this->rewardsRepository->getCustomerRewardBalance($item["entity_id"]);
                $item[self::FIELD_NAME] = $rewardBalance;
            }
        }

        return $dataSource;
    }
}
