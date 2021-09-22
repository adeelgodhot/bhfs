<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Subscription;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterfaceFactory;
use Amasty\RecurringPayments\Model\Repository\AddressRepository;
use Amasty\RecurringPayments\Model\Subscription\GridSource;
use Amasty\RecurringStripe\Api\ProductRepositoryInterface as StripeProductRepository;
use Amasty\RecurringStripe\Api\CustomerRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterfaceFactory;
use Amasty\RecurringPayments\Api\Subscription\GridInterface;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringStripe\Model\Adapter;
use Amasty\RecurringStripe\Model\Subscription\Cache as SubscriptionCache;
use Amasty\Stripe\Api\Data\CustomerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class Grid extends GridSource implements GridInterface
{
    const ACTIVE_STATUSES = [
        StatusMapper::ACTIVE,
        StatusMapper::TRIAL,
    ];
    const MAX_LIMIT = 100;

    /**
     * @var array
     */
    private $stripeProducts = [];

    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionFactory;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StripeProductRepository
     */
    private $stripeProductRepository;

    /**
     * @var AddressRepository
     */
    private $addressRepository;

    /**
     * @var SubscriptionInfoInterfaceFactory
     */
    private $subscriptionInfoFactory;

    /**
     * @var Cache
     */
    private $subscriptionCache;

    /**
     * @var InvoiceInfoFactory
     */
    private $infoFactory;

    public function __construct(
        Date $date,
        PriceCurrencyInterface $priceCurrency,
        CountryFactory $countryFactory,
        SubscriptionInterfaceFactory $subscriptionFactory,
        Adapter $adapter,
        CustomerRepositoryInterface $customerRepository,
        StatusMapper $statusMapper,
        OrderFactory $orderFactory,
        UrlInterface $urlBuilder,
        RepositoryInterface $subscriptionRepository,
        ProductRepositoryInterface $productRepository,
        StripeProductRepository $stripeProductRepository,
        AddressRepository $addressRepository,
        SubscriptionInfoInterfaceFactory $subscriptionInfoFactory,
        InvoiceInfoFactory $infoFactory,
        SubscriptionCache $subscriptionCache
    ) {
        parent::__construct($date, $priceCurrency, $countryFactory);
        $this->subscriptionFactory = $subscriptionFactory;
        $this->adapter = $adapter;
        $this->customerRepository = $customerRepository;
        $this->statusMapper = $statusMapper;
        $this->orderFactory = $orderFactory;
        $this->urlBuilder = $urlBuilder;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->productRepository = $productRepository;
        $this->stripeProductRepository = $stripeProductRepository;
        $this->addressRepository = $addressRepository;
        $this->subscriptionInfoFactory = $subscriptionInfoFactory;
        $this->subscriptionCache = $subscriptionCache;
        $this->infoFactory = $infoFactory;
    }

    /**
     * @param int $customerId
     * @return SubscriptionInterface[]
     */
    public function process(int $customerId)
    {
        $subscriptions = [];
        $latestInvoice = null;

        /** @var CustomerInterface $customer */
        try {
            $customer = $this->customerRepository->getStripeCustomer($customerId, $this->adapter->getAccountId());
        } catch (NoSuchEntityException $e) {
            return $subscriptions;
        }

        /** @var \Stripe\Collection $subscriptionsStripe */
        $subscriptionsStripe = $this->adapter->subscriptionList([
            'customer'          => $customer->getStripeCustomerId(),
            'collection_method' => 'charge_automatically',
            'status'            => 'all',
            'limit'             => self::MAX_LIMIT
        ]);

        if (empty($subscriptionsStripe->data)) {
            return $subscriptions;
        }

        /** @var \Stripe\Subscription $subscriptionStripe */
        foreach ($subscriptionsStripe->data as $subscriptionStripe) {
            /** @var SubscriptionInfoInterface $subscriptionInfo */
            $subscriptionInfo = $this->subscriptionInfoFactory->create();

            if ($latestInvoiceId = $subscriptionStripe->latest_invoice) {
                $latestInvoice = $this->getInvoiceInfo((string)$latestInvoiceId);
            }

            try {
                /** @var SubscriptionInterface $subscription */
                $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionStripe->id);
            } catch (NoSuchEntityException $exception) {
                /** @var SubscriptionInterface $subscription */
                $subscription = $this->subscriptionFactory->create();
                $subscription->setSubscriptionId($subscriptionStripe->id);
                $subscription->setStartDate(\date('Y-m-d H:i:s', $subscriptionStripe->created));
            }

            $isTrialFake = !$subscription->getTrialDays();

            $subscriptionInfo->setSubscription($subscription);
            if ($address = $this->findAddress($subscription, $subscriptionStripe->id)) {
                $this->setStreet($address);
                $this->setCountry($address);
                $subscriptionInfo->setAddress($address);
            }

            if (in_array($subscriptionStripe->status, self::ACTIVE_STATUSES)) {
                $this->setNextInvoice($subscriptionInfo);
                $subscriptionInfo->setIsActive(true);
            } else {
                $subscriptionInfo->setIsActive(false);
            }

            $subscriptionInfo->setOrderIncrementId((string)$subscriptionStripe->metadata->increment_id);
            $subscriptionInfo->setOrderLink($this->getOrderLink((string)$subscriptionStripe->metadata->increment_id));
            $subscriptionInfo->setSubscriptionName($this->getProductName($subscriptionStripe->plan));
            $subscriptionInfo->setStartDate($this->formatDate(strtotime($subscription->getStartDate())));

            $subscription->setQty($subscriptionStripe->quantity);
            $subscription->setDelivery((string)$subscriptionStripe->metadata->delivery);
            !$isTrialFake && $this->setTrial($subscriptionInfo, $subscriptionStripe);

            if ($latestInvoice && $latestInvoice->getAmount() > 0.0001) {
                $subscriptionInfo->setLastBilling($this->formatDate((int)$latestInvoice->getDate()));
                $subscriptionInfo->setLastBillingAmount($this->getBillingAmount($latestInvoice));
            }

            $status = $subscriptionStripe->status;
            if ($isTrialFake && $status == StatusMapper::TRIAL) {
                $status = StatusMapper::ACTIVE;
            }
            $subscriptionInfo->setStatus($this->statusMapper->getStatus($status));
            $subscription->setPaymentMethod(\Amasty\Stripe\Model\Ui\ConfigProvider::CODE);
            $subscriptions[] = $subscriptionInfo;
        }

        return $subscriptions;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $subscriptionId
     * @return \Amasty\RecurringPayments\Api\Subscription\AddressInterface|null
     */
    private function findAddress(SubscriptionInterface $subscription, string $subscriptionId)
    {
        if ($addressId = $subscription->getAddressId()) { // Since 1.1.1
            try {
                return $this->addressRepository->getById($addressId);
            } catch (NoSuchEntityException $exception) {
                return null;
            }
        } else { // Compatibility with pre 1.1.1
            try {
                return $this->addressRepository->getBySubscriptionId($subscriptionId);
            } catch (NoSuchEntityException $exception) {
                return null;
            }
        }
    }

    /**
     * @param string $invoiceId
     * @return InvoiceInfo
     */
    private function getInvoiceInfo(string $invoiceId): InvoiceInfo
    {
        $info = $this->subscriptionCache->getInvoiceInfo($invoiceId);

        if (!$info) {
            /** @var \Stripe\Invoice $invoice */
            $invoice = $this->adapter->invoiceRetrieve($invoiceId);
            /** @var InvoiceInfo $info */
            $info = $this->infoFactory->create();
            $info->setId((string)$invoiceId)
                ->setDate((int)$invoice->date)
                ->setAmount((float)$invoice->amount_due)
                ->setCurrency((string)$invoice->currency);
            $this->subscriptionCache->saveInvoiceInfo($invoiceId, $info);
        }

        return $info;
    }

    /**
     * @param string $subscriptionId
     * @return InvoiceInfo
     */
    private function getUpcomingInvoiceInfo(string $subscriptionId): InvoiceInfo
    {
        $info = $this->subscriptionCache->getInvoiceInfo($subscriptionId);

        if (!$info || $info->getDate() < time()) { // Invalidate cache if next billing date is in past
            /** @var \Stripe\Invoice $invoice */
            $invoice = $this->adapter->upcomingInvoiceRetrieve(['subscription' => $subscriptionId]);

            /** @var InvoiceInfo $info */
            $info = $this->infoFactory->create();
            $info->setId('')
                ->setDate((int)$invoice->date)
                ->setAmount((float)$invoice->amount_due)
                ->setCurrency((string)$invoice->currency);
            $this->subscriptionCache->saveInvoiceInfo($subscriptionId, $info);
        }

        return $info;
    }

    /**
     * @param SubscriptionInfoInterface $subscriptionInfo
     * @param \Stripe\Subscription $subscriptionStripe
     */
    private function setTrial(SubscriptionInfoInterface $subscriptionInfo, \Stripe\Subscription $subscriptionStripe)
    {
        if ($subscriptionStripe->status === StatusMapper::TRIAL) {
            $subscription = $subscriptionInfo->getSubscription();
            $startDateTimestamp = strtotime($subscription->getStartDate());
            if ($startDateTimestamp > $subscriptionStripe->trial_start) {
                $subscriptionInfo->setTrialStartDate($this->formatDate($startDateTimestamp));
            } else {
                $subscriptionInfo->setTrialStartDate($this->formatDate($subscriptionStripe->trial_start));
            }
            $subscriptionInfo->setTrialEndDate($this->formatDate($subscriptionStripe->trial_end));
        }
    }

    /**
     * @param string $incrementId
     * @return string
     */
    private function getOrderLink(string $incrementId): string
    {
        /** @var Order $order */
        $order = $this->orderFactory->create();

        $order->loadByIncrementId($incrementId);

        return $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @param SubscriptionInfoInterface $subscription
     */
    private function setNextInvoice(
        SubscriptionInfoInterface $subscription
    ) {
        try {
            $invoiceInfo = $this->getUpcomingInvoiceInfo($subscription->getSubscription()->getSubscriptionId());
        } catch (\Exception $e) {
            return;
        }

        $subscription->setNextBilling($this->formatDate((int)$invoiceInfo->getDate()));
        $subscription->setNextBillingAmount($this->getBillingAmount($invoiceInfo));
    }

    /**
     * @param \Stripe\Plan $plan
     * @return string
     */
    private function getProductName(\Stripe\Plan $plan): string
    {
        $productId = $plan->product;

        if (!isset($this->stripeProducts[$productId])) {
            $stripeProduct = $this->stripeProductRepository->getByStripeProdId($productId);
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->getById($stripeProduct->getProductId());
            $this->stripeProducts[$productId] = $product->getName();
        }

        return $this->stripeProducts[$productId];
    }

    /**
     * @param InvoiceInfo $invoiceInfo
     * @return string
     */
    private function getBillingAmount(InvoiceInfo $invoiceInfo): string
    {
        return $this->formatPrice(
            $invoiceInfo->getAmount() / \Amasty\RecurringPayments\Model\Amount::PERCENT,
            mb_strtoupper($invoiceInfo->getCurrency())
        );
    }
}
