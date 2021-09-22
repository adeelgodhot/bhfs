<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Plugin;

use Amasty\Followup\Model\Rule as Rule;

class SaveProduct
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Amasty\Followup\Model\ScheduleFactory
     */
    protected $scheduleFactory;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Amasty\Followup\Model\ScheduleFactory $scheduleFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
        $this->scheduleFactory = $scheduleFactory;
        $this->customerFactory = $customerFactory;
    }

    public function afterBeforeSave(\Magento\Catalog\Model\Product $subject)
    {
        if (!$subject->getData("id")) {
            return $subject;
        }

        $oldData = $subject->getOrigData();

        $oldSpecialPrice = isset($oldData['special_price']) ? $oldData['special_price'] : null;
        $newSpecialPrice = $subject->getSpecialPrice();

        $oldSpecialFromDate = isset($oldData['special_from_date']) ? $oldData['special_from_date'] : null;
        $newSpecialFromDate = $subject->getSpecialFromDate();

        $oldSpecialToDate = isset($oldData['special_to_date']) ? $oldData['special_to_date'] : null;
        $newSpecialToDate = $subject->getSpecialToDate();

        $onSale = false;
        $backInstock = false;

        if ($oldSpecialPrice != $newSpecialPrice
            || $oldSpecialFromDate != $newSpecialFromDate
            || $oldSpecialToDate != $newSpecialToDate
        ) {
            $onSale = true;
        }

        $oldIsInStock = isset($oldData['quantity_and_stock_status'])
            && isset($oldData['quantity_and_stock_status']['is_in_stock'])
            ? $oldData['quantity_and_stock_status']['is_in_stock']
            : null;
        $qtyAndStock = $subject->getQuantityAndStockStatus();
        $newIsInStock = isset($qtyAndStock['is_in_stock']) ? $qtyAndStock['is_in_stock'] : null;

        if ($oldIsInStock != $newIsInStock && $newIsInStock == 1) {
            $backInstock = true;
        }

        if ($onSale || $backInstock) {
            $types = [];

            if ($onSale) {
                $types[] = Rule::TYPE_CUSTOMER_WISHLIST_SALE;
            } elseif($backInstock) {
                $types[] = Rule::TYPE_CUSTOMER_WISHLIST_BACK_INSTOCK;
            }

            $collection = $this->objectManager
                ->create('Magento\Wishlist\Model\ResourceModel\Wishlist\Collection');

            $collection->getSelect()->joinLeft(
                array('wishlist_item' => $collection->getTable('wishlist_item')),
                'main_table.wishlist_id = wishlist_item.wishlist_id',
                array()
            );
            $collection->getSelect()->where('wishlist_item.product_id = ' . $subject->getId());

            if ($collection->getSize()) {
                foreach ($collection as $model) {
                    $customer = $this->customerFactory->create()->load((int)$model->getCustomerId());

                    $this->scheduleFactory->create()
                        ->checkCustomerRules
                        (
                            $customer,
                            $types,
                            $subject
                        )
                    ;
                }
            }

        }

        return $subject;

    }
}