<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Email\Items;

class Wishlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    private $wishlistFactory;

    /**
     * Wishlist constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->objectManager = $objectManager;
        $this->wishlistFactory = $wishlistFactory;
    }

    /**
     * @return \Magento\Wishlist\Model\ResourceModel\Item\Collection
     */
    public function getItems()
    {
        $wishlist = $this->wishlistFactory->create()->loadByCustomerId($this->getCustomer()->getId());

        return $wishlist->getItemCollection();
    }
}