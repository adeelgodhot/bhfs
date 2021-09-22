<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Ui\Component\Listing\Column;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface as RecurringAttributes;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class RecurringStatus

 */
class RecurringStatus extends Column
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductRepositoryInterface $productRepository,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productRepository = $productRepository;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as &$row) {
                try {
                    /** @var \Magento\Catalog\Model\Product $product */
                    $product = $this->productRepository->getById($row['entity_id']);
                    $recurringStatus = $product->getData(RecurringAttributes::RECURRING_ENABLE);

                    if (!$recurringStatus || $recurringStatus === AvailableSubscription::NO) {
                        $row[RecurringAttributes::RECURRING_ENABLE] = __('No');
                    }

                    if ($recurringStatus === AvailableSubscription::GLOBAL_SETTING) {
                        $row[RecurringAttributes::RECURRING_ENABLE] = $this->config->isSubscriptionOnly()
                            ? __('Yes, subscription only')
                            : __('Yes');
                    }

                    if ($recurringStatus === AvailableSubscription::CUSTOM_SETTING) {
                        $recurringType = $product->getData(RecurringAttributes::SUBSCRIPTION_ONLY);
                        $row[RecurringAttributes::RECURRING_ENABLE] = $recurringType
                            ? __('Yes, subscription only')
                            : __('Yes');
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                    $row[RecurringAttributes::RECURRING_ENABLE] = __('No');
                }
            }

            unset($row);
        }

        return $dataSource;
    }
}
