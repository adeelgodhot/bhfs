<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Magento\Customer\Api\Data\AddressExtensionFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory as AddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as RegionFactory;
use Magento\Framework\DataObject\Copy as CopyService;
use Magento\Sales\Api\Data\OrderAddressInterface;

class CustomerFromOrderAddressConverter
{
    /**
     * @var CopyService
     */
    private $objectCopyService;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var AddressExtensionFactory
     */
    private $addressExtensionFactory;

    public function __construct(
        CopyService $objectCopyService,
        AddressFactory $addressFactory,
        RegionFactory $regionFactory,
        AddressExtensionFactory $addressExtensionFactory
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->addressExtensionFactory = $addressExtensionFactory;
    }

    /**
     * @param OrderAddressInterface $orderAddress
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function convert(OrderAddressInterface $orderAddress): \Magento\Customer\Api\Data\AddressInterface
    {
        $addressData = $this->objectCopyService
            ->copyFieldsetToTarget('order_address', 'to_customer_address', $orderAddress, []);

        $addressData = array_filter($addressData, function ($value) {
            return $value !== null;
        });

        foreach ($addressData['custom_attributes'] ?? [] as $key => $customAttribute) {
            if ($customAttribute === null) {
                unset($addressData['custom_attributes'][$key]);
            }
        }

        // create new customer address only if it is unique
        $customerAddress = $this->addressFactory->create(['data' => $addressData]);

        if (is_string($orderAddress->getRegion())) {
            $region = $this->regionFactory->create();
            $region->setRegion($orderAddress->getRegion());
            $region->setRegionCode($orderAddress->getRegionCode());
            $region->setRegionId($orderAddress->getRegionId());
            $customerAddress->setRegion($region);
        }

//        if (!$customerAddress->getExtensionAttributes()) {
//            $customerAddress->setExtensionAttributes(
//                $this->addressExtensionFactory->create()
//            );
//        }

        return $customerAddress;
    }
}
