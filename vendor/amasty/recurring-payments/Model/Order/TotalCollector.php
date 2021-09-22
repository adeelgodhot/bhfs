<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Order;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping;
use Magento\Quote\Model\Quote\Address\Total\ShippingFactory as ShippingTotalFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;

class TotalCollector
{
    /**
     * @var TotalFactory
     */
    private $totalFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingTotalFactory
     */
    private $shipTotalFactory;

    public function __construct(
        TotalFactory $totalFactory,
        ShippingFactory $shippingFactory,
        ShippingAssignmentFactory $shippingAssignmentFactory,
        ShippingTotalFactory $shipTotalFactory
    ) {
        $this->totalFactory = $totalFactory;
        $this->shippingFactory = $shippingFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shipTotalFactory = $shipTotalFactory;
    }

    /**
     * @param CartInterface $quote
     *
     * @return Shipping
     */
    public function collect(CartInterface $quote)
    {
        /** @var Address $address */
        $address = $quote->getShippingAddress();
        $address->setCollectShippingRates(true);
        $address->setData('cached_items_all', $quote->getAllItems());
        $address->setBaseSubtotal($quote->getBaseSubtotal());

        /** @var Total $total */
        $total = $this->totalFactory->create(Total::class);

        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->shippingAssignmentFactory->create();

        /** @var ShippingInterface $shipping */
        $shipping = $this->shippingFactory->create();
        $shipping->setMethod($this->getShippingMethod($quote));
        $shipping->setAddress($address);
        $shippingAssignment->setShipping($shipping);

        $shippingAssignment->setItems($quote->getAllItems());

        /** @var Shipping $collector */
        $collector = $this->shipTotalFactory->create();

        return $collector->collect($quote, $shippingAssignment, $total);
    }

    /**
     * @param CartInterface $quote
     *
     * @return string
     */
    private function getShippingMethod(CartInterface $quote): string
    {
        $shippingMethod = '';

        if ($shippingAssignment = $quote->getExtensionAttributes()->getShippingAssignments()) {
            /** @var \Magento\Quote\Model\Shipping $shipping */
            $shipping = $shippingAssignment[0]->getShipping();
            $shippingMethod = $shipping->getMethod();
        }

        return $shippingMethod;
    }
}
