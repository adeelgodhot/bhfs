<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Subscription;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterfaceFactory;
use Amasty\RecurringPayments\Model\Subscription\GridSource;
use Amasty\RecurringPaypal\Model\Processor\CreatePlan;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\GridInterface;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PayPal\Exception\PayPalConnectionException;

class Grid extends GridSource implements GridInterface
{
    const SECS_IN_DAY = 3600 * 24;
    const ACTIVE_STATUSES = [
        StatusMapper::ACTIVE,
        StatusMapper::APPROVAL_PENDING,
    ];

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubscriptionInfoInterfaceFactory
     */
    private $subscriptionInfoFactory;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Cache
     */
    private $subscriptionCache;

    public function __construct(
        Date $date,
        PriceCurrencyInterface $priceCurrency,
        CountryFactory $countryFactory,
        Adapter $adapter,
        StatusMapper $statusMapper,
        UrlInterface $urlBuilder,
        RepositoryInterface $subscriptionRepository,
        ProductRepositoryInterface $productRepository,
        SubscriptionInfoInterfaceFactory $subscriptionInfoFactory,
        AddressRepositoryInterface $addressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubscriptionCache $subscriptionCache,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($date, $priceCurrency, $countryFactory);
        $this->adapter = $adapter;
        $this->statusMapper = $statusMapper;
        $this->urlBuilder = $urlBuilder;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subscriptionInfoFactory = $subscriptionInfoFactory;
        $this->addressRepository = $addressRepository;
        $this->orderRepository = $orderRepository;
        $this->subscriptionCache = $subscriptionCache;
    }

    /**
     * @inheritDoc
     */
    public function process(int $customerId)
    {
        $result = [];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                SubscriptionInterface::CUSTOMER_ID,
                $customerId
            )->addFilter(
                SubscriptionInterface::PAYMENT_METHOD,
                ['paypal_express', 'amasty_recurring_paypal'],
                'in'
            )->create();

        $subscriptions = $this->subscriptionRepository->getList($searchCriteria);
        $orders = $this->getRelatedOrders($subscriptions->getItems());
        $products = $this->getRelatedProducts($subscriptions->getItems());

        /** @var SubscriptionInterface $subscription */
        foreach ($subscriptions->getItems() as $subscription) {
            /** @var OrderInterface $order */
            $order = $orders[$subscription->getOrderId()] ?? null;
            /** @var Product $product */
            $product = $products[$subscription->getProductId()] ?? null;
            $subscriptionInfo = $this->subscriptionInfoFactory->create();
            $subscriptionInfo->setSubscription($subscription);

            if ($address = $this->findAddress($subscription)) {
                $subscriptionInfo->setAddress($address);
                $this->setStreet($address);
                $this->setCountry($address);
            }

            try {
                $cachedValue = $this->subscriptionCache->getSubscriptionData($subscription->getSubscriptionId());
                if ($cachedValue === false) {
                    $details = $this->adapter->getSubscriptionDetails($subscription->getSubscriptionId());
                    $this->subscriptionCache->saveSubscriptionData($details);
                } elseif (SubscriptionCache::BROKEN_RECORD === $cachedValue) {
                    continue;
                } else {
                    $details = $cachedValue;
                }
            } catch (PayPalConnectionException $e) {
                $this->subscriptionCache->markAsBroken($subscription->getSubscriptionId());
                continue; // Accidental paypal errors, just keep going ¯\_(ツ)_/¯
            }

            if (in_array($details['status'], self::ACTIVE_STATUSES)) {
                if ($nextBilling = $details['billing_info']['next_billing_time'] ?? null) {
                    if (strtotime($nextBilling) >= time()) {
                        $subscriptionInfo->setNextBilling($this->formatDate(strtotime($nextBilling)));
                        $baseNextBillingAmount = (float)$subscription->getBaseGrandTotalWithDiscount();

                        if ($subscription->getRemainingDiscountCycles() !== null
                            && $subscription->getRemainingDiscountCycles() < 1
                        ) {
                            $baseNextBillingAmount = (float)$subscription->getBaseGrandTotal();
                        }
                        $subscriptionInfo->setNextBillingAmount(
                            $this->formatPrice(
                                $baseNextBillingAmount,
                                $order->getOrderCurrencyCode()
                            )
                        );
                    }
                }

                $subscriptionInfo->setIsActive(true);
            } else {
                $subscriptionInfo->setIsActive(false);
            }

            if ($order) {
                $subscriptionInfo->setOrderIncrementId($order->getIncrementId());
                $subscriptionInfo->setOrderLink(
                    $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()])
                );
            }
            $subscriptionInfo->setSubscriptionName($product->getName());
            $subscriptionInfo->setStartDate($this->formatDate(strtotime($details['create_time'])));

            $this->setTrial($subscriptionInfo, $details);

            if ($lastBilling = $details['billing_info']['last_payment'] ?? null) {
                $subscriptionInfo->setLastBilling($this->formatDate(strtotime($lastBilling['time'])));
                $subscriptionInfo->setLastBillingAmount(
                    $this->formatPrice((float)$lastBilling['amount']['value'], $lastBilling['amount']['currency_code'])
                );
            }

            $subscriptionInfo->setStatus($this->statusMapper->getStatus($details['status']));
            $this->setApprovalLink($subscriptionInfo, $details);

            $result[] = $subscriptionInfo;
        }

        return $result;
    }

    private function isTrialActive(SubscriptionInfoInterface $subscriptionInfo, array $details): bool
    {
        $firstCycleType = $details['billing_info']['cycle_executions'][0]['tenure_type']
            ?? CreatePlan::CYCLE_TYPE_REGULAR;

        return $firstCycleType == CreatePlan::CYCLE_TYPE_TRIAL && !$subscriptionInfo->getLastBilling();
    }

    private function setApprovalLink(SubscriptionInfoInterface $subscriptionInfo, array $details)
    {
        foreach ($details['links'] as $link) {
            if ($link['rel'] == 'approve') {
                $subscriptionInfo->setApprovalLink($link['href']);
                return;
            }
        }
    }

    private function getRelatedOrders(array $subscriptions): array
    {
        $orderIds = array_map(
            function (SubscriptionInterface $subscription) {
                return $subscription->getOrderId();
            },
            $subscriptions
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $orderIds, 'in')
            ->create();

        $searchResult = $this->orderRepository->getList($searchCriteria);

        return $searchResult->getItems();
    }

    private function getRelatedProducts(array $subscriptions): array
    {
        $productIds = array_map(
            function (SubscriptionInterface $subscription) {
                return $subscription->getProductId();
            },
            $subscriptions
        );

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        $searchResult = $this->productRepository->getList($searchCriteria);

        return $searchResult->getItems();
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return AddressInterface|null
     */
    private function findAddress(SubscriptionInterface $subscription)
    {
        if ($addressId = $subscription->getAddressId()) {
            try {
                return $this->addressRepository->getById($addressId);
            } catch (NoSuchEntityException $exception) {
                return null;
            }
        }

        return null;
    }

    private function setTrial(SubscriptionInfoInterface $subscriptionInfo, array $details)
    {
        if ($this->isTrialActive($subscriptionInfo, $details)
            && in_array($details['status'], self::ACTIVE_STATUSES)
        ) {
            $subscriptionInfo->setTrialStartDate($subscriptionInfo->getStartDate());
            $endTimestamp = strtotime($details['create_time'])
                + $subscriptionInfo->getSubscription()->getTrialDays() * self::SECS_IN_DAY;
            $endDate = $this->formatDate($endTimestamp);
            $subscriptionInfo->setTrialEndDate($endDate);
        }
    }
}
