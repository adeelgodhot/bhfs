<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class Wishlist extends \Amasty\Followup\Model\Event\Basic
{
    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    private $wishlist;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Followup\Model\Rule $rule,
        \Amasty\Followup\Helper\Data $helper,
        \Magento\Sales\Model\Order\Status\History $statusHistory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomerFactory,
        \Amasty\Followup\Model\Factories\SegmentFactory $segmentFactory,
        \Magento\Framework\FlagFactory $flagManagerFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        $status = null,
        array $data = []
    ) {
        parent::__construct(
            $storeManager,
            $rule,
            $helper,
            $statusHistory,
            $configInterface,
            $date,
            $dateTime,
            $objectManager,
            $order,
            $collectionCustomerFactory,
            $segmentFactory,
            $flagManagerFactory,
            $status,
            $data
        );
        $this->wishlist = $wishlistFactory->create();
    }

    /**
     * @param $customer
     * @return bool
     */
    public function validate($customer)
    {
        $wishlist = $this->wishlist->loadByCustomerId($customer->getId());
        $validateBasic = $this->_validateBasic(
            $customer->getStoreId(),
            $customer->getEmail(),
            $customer->getGroupId()
        );

        $validateWishlist = $wishlist->getItemsCount() > 0 && $validateBasic;

        if ($validateWishlist) {
            $this->cancelEventWishlist();
        }

        return $validateWishlist;
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function _initCollection()
    {
        $collection = $this->collectionCustomerFactory->create();
        $collection->addNameToSelect();

        $collection->getSelect()->joinInner(
            ['wishlist' => $collection->getTable('wishlist')],
            'e.entity_id = wishlist.customer_id',
            []
        );

        $collection->getSelect()->where(
            "wishlist.updated_at > '"
            . $this->_dateTime->formatDate($this->getLastExecuted())
            . "'"
        );

        $collection->getSelect()->where(
            "wishlist.updated_at < '"
            . $this->_dateTime->formatDate($this->getCurrentExecution())
            . "'"
        );

        $collection->getSelect()->group("e.entity_id");

        return $collection;
    }
}
