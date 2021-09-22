<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Sales\Order;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\AddressFactory as QuoteAddressFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Amasty\ThankYouPage\Model\Sales\Order\OrderCustomerExtractor as OrderCustomerExtractorMageLte22;
use Magento\Sales\Model\Order\OrderCustomerExtractor as MageOrderCustomerExtractor;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class CustomerManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerManagement implements \Amasty\ThankYouPage\Api\OrderCustomerManagementInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Copy
     */
    protected $objectCopyService;

    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var MageOrderCustomerExtractor
     */
    private $customerExtractor;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        Copy $objectCopyService,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerFactory,
        AddressInterfaceFactory $addressFactory,
        RegionInterfaceFactory $regionFactory,
        OrderRepositoryInterface $orderRepository,
        ManagerInterface $eventManager,
        ObjectManagerInterface $objectManager,
        QuoteAddressFactory $quoteAddressFactory = null
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->accountManagement = $accountManagement;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->eventManager = $eventManager;

        if (class_exists(QuoteAddressFactory::class)) {
            $this->quoteAddressFactory = $quoteAddressFactory
                ?: ObjectManager::getInstance()->get(QuoteAddressFactory::class);
        }

        // Backwards compatibility with Magento 2.1
        $this->customerExtractor = class_exists(MageOrderCustomerExtractor::class)
            ? $objectManager->get(MageOrderCustomerExtractor::class)
            : $objectManager->get(OrderCustomerExtractorMageLte22::class);
    }

    /**
     * @param string $orderId
     * @param null|string $email
     * @param null|string $password
     * @param null|string $dateOfBirth
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create($orderId, $email = null, $password = null, $dateOfBirth = null)
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getCustomerId()) {
            throw new AlreadyExistsException(
                __('This order already has associated customer account')
            );
        }

        $customer = $this->customerExtractor->extract($orderId);
        /** @var AddressInterface[] $filteredAddresses */
        $filteredAddresses = [];
        foreach ($customer->getAddresses() as $address) {
            if ($this->needToSaveAddress($order, $address)) {
                $filteredAddresses[] = $address;
            }
        }
        $customer->setAddresses($filteredAddresses);

        if ($email) {
            $customer->setEmail($email);
        }

        if ($dateOfBirth) {
            $customer->setDob($dateOfBirth);
        }

        $account = $this->accountManagement->createAccount($customer, $password);

        // for custom events when execute \Magento\Customer\Controller\Account\CreatePost controller
        $this->eventManager->dispatch(
            'customer_register_success',
            [
                'customer' => $account,
                'amasty_checkout_register' => true
            ]
        );

        $order = $this->orderRepository->get($orderId);
        $order->setCustomerId($account->getId());
        $order->setCustomerIsGuest(0);
        $this->orderRepository->save($order);

        return $account;
    }

    /**
     * @param OrderInterface $order
     * @param AddressInterface $address
     *
     * @return bool
     */
    private function needToSaveAddress(
        OrderInterface $order,
        AddressInterface $address
    ) {
        /** @var OrderAddressInterface|null $orderAddress */
        $orderAddress = null;
        if ($address->isDefaultBilling()) {
            $orderAddress = $order->getBillingAddress();
        } elseif ($address->isDefaultShipping()) {
            $orderAddress = $order->getShippingAddress();
        }
        if ($orderAddress) {
            $quoteAddressId = $orderAddress->getData('quote_address_id');
            if ($quoteAddressId) {
                // Backwards compatibility with Magento 2.1
                /** @var QuoteAddress $quote */
                $quoteAddress = $this->quoteAddressFactory
                    ? $this->quoteAddressFactory->create()
                    : ObjectManager::getInstance()->create(QuoteAddress::class);
                $quoteAddress->load($quoteAddressId);
                if ($quoteAddress && $quoteAddress->getId()) {
                    return (bool)(int)$quoteAddress->getData('save_in_address_book');
                }
            }

            return true;
        }

        return false;
    }
}
