<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\SubscriptionPlan\DataProvider;

use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Amasty\RecurringPayments\Model\SubscriptionPlan;
use Magento\Framework\App\Request\DataPersistorInterface;

class SubscriptionPlanDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var SubscriptionPlan $subscriptionPlan */
        foreach ($items as $subscriptionPlan) {
            $this->loadedData[$subscriptionPlan->getId()] = $subscriptionPlan->getData();
        }

        $data = $this->dataPersistor->get('amasty_recurring_payments_subscription_plan');
        if (!empty($data)) {
            $subscriptionPlan = $this->collection->getNewEmptyItem();
            $subscriptionPlan->setData($data);
            $this->loadedData[$subscriptionPlan->getId()] = $subscriptionPlan->getData();
            $this->dataPersistor->clear('amasty_recurring_payments_subscription_plan');
        }

        return $this->loadedData;
    }
}
