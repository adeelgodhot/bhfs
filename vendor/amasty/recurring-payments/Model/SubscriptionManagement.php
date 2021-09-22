<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressInterfaceFactory;
use Amasty\RecurringPayments\Api\Subscription\AddressRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\GridInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface as SubscriptionRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterfaceFactory;
use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Amasty\RecurringPayments\Model\Subscription\Mapper\StartEndDateMapper;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class SubscriptionManagement
{
    const TYPE_OPTIONS = [
        Type::TYPE_BUNDLE                   => ['bundle_option', 'bundle_option_qty'],
        DownloadableType::TYPE_DOWNLOADABLE => ['links'],
        Configurable::TYPE_CODE             => ['super_attribute']
    ];

    /**
     * @var GridInterface[]
     */
    private $subscriptionProcessors;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var SubscriptionInterfaceFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var StartEndDateMapper
     */
    private $startEndDateMapper;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    /**
     * @var QuoteGenerator
     */
    private $quoteGenerator;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressFactory,
        SubscriptionInterfaceFactory $subscriptionFactory,
        SubscriptionRepositoryInterface $subscriptionRepository,
        SerializerInterface $serializer,
        StartEndDateMapper $startEndDateMapper,
        ItemDataRetriever $itemDataRetriever,
        Amount $amount,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        QuoteGenerator $quoteGenerator,
        Config $config,
        array $subscriptionProcessors = []
    ) {
        $this->subscriptionProcessors = $subscriptionProcessors;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->serializer = $serializer;
        $this->startEndDateMapper = $startEndDateMapper;
        $this->itemDataRetriever = $itemDataRetriever;
        $this->amount = $amount;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
        $this->quoteGenerator = $quoteGenerator;
        $this->config = $config;
    }

    /**
     * @param int $customerId
     *
     * @return array
     */
    public function getSubscriptions(int $customerId): array
    {
        $subscriptions = [];

        /** @var GridInterface $processor */
        foreach ($this->subscriptionProcessors as $processor) {
            try {
                $subscriptions[] = $processor->process($customerId);
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        if (empty($subscriptions)) {
            return [];
        }

        $subscriptions = array_merge(...$subscriptions);

        usort(
            $subscriptions,
            function (SubscriptionInfoInterface $a, SubscriptionInfoInterface $b) {
                return -(strtotime($a->getStartDate()) <=> strtotime($b->getStartDate()));
            }
        );

        return $subscriptions;
    }

    /**
     * @param OrderInterface $order
     * @param Quote\Item\AbstractItem $item
     * @return SubscriptionInterface
     */
    public function generateSubscription(
        OrderInterface $order,
        Quote\Item\AbstractItem $item
    ): SubscriptionInterface {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $item->getQuote();
        $product = $item->getProduct();
        $subscriptionPlan = $this->itemDataRetriever->getPlan($item, false);

        /** @var SubscriptionInterface $subscription */
        $subscription = $this->subscriptionFactory->create();

        $trialDays = $subscriptionPlan->getEnableTrial()
            ? $subscriptionPlan->getTrialDays()
            : 0;
        $initialFee = 0;
        if ($subscriptionPlan->getEnableInitialFee() && $subscriptionPlan->getInitialFeeAmount()) {
            $initialFee = $this->amount->getAmount(
                $product,
                (float)$subscriptionPlan->getInitialFeeAmount(),
                $subscriptionPlan->getInitialFeeType(),
                $item
            );
        }

        list($startDate, $endDate) = $this->startEndDateMapper->getStartEndDate($item);
        $timezoneName = $this->startEndDateMapper->getSpecifiedTimezone($item);
        $discountCycles = $this->calculateActualCouponUsages($subscriptionPlan);

        $estimationQuote = $this->quoteGenerator->generateFromItem($item, true);
        $baseGrandTotal = $baseGrandTotalWithDiscount = $estimationQuote->getBaseGrandTotal();
        $baseDiscountAmount = 0.0;
        if ($subscriptionPlan->getEnableDiscount()
            && $subscriptionPlan->getDiscountAmount()
            && ($discountCycles === null || $discountCycles > 0)
        ) {
            $estimationQuoteWithDiscount = $this->quoteGenerator->generateFromItem($item, false);
            /** @var \Magento\Quote\Model\Quote\Item $estimationItem */
            $estimationItem = $estimationQuoteWithDiscount->getAllVisibleItems()[0];
            $baseGrandTotalWithDiscount = $estimationQuoteWithDiscount->getBaseGrandTotal();
            $baseDiscountAmount = $estimationItem->getBaseDiscountAmount();
            if ($estimationItem->getHasChildren() && $estimationItem->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseDiscountAmount += $child->getBaseDiscountAmount();
                }
            }
        }

        $delivery = $this->billingFrequencyLabelMapper->getLabel(
            $subscriptionPlan->getFrequency(),
            $subscriptionPlan->getFrequencyUnit()
        );

        $subscription
            ->setPaymentMethod($order->getPayment()->getMethod())
            ->setCustomerId((int)$order->getCustomerId())
            ->setOrderId((int)$order->getEntityId())
            ->setProductId((int)$product->getId())
            ->setProductOptions($this->serializer->serialize($this->getItemOptions($item)))
            ->setStoreId((int)$quote->getStoreId())
            ->setShippingMethod($order->getShippingMethod())
            ->setFreeShipping($this->config->isEnableFreeShipping())
            ->setDelivery($delivery)
            ->setQty($item->getQty())
            ->setBaseDiscountAmount($baseDiscountAmount)
            ->setBaseGrandTotal($baseGrandTotal)
            ->setBaseGrandTotalWithDiscount($baseGrandTotalWithDiscount)
            ->setInitialFee($initialFee)
            ->setTrialDays($trialDays)
            ->setRemainingDiscountCycles($discountCycles)
            ->setCountDiscountCycles($discountCycles)
            ->setStatus(SubscriptionInterface::STATUS_ACTIVE)
            ->setFrequency($subscriptionPlan->getFrequency())
            ->setFrequencyUnit($subscriptionPlan->getFrequencyUnit())
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setCustomerTimezone($timezoneName)
            ->setCustomerEmail($order->getCustomerEmail());

        return $subscription;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param OrderInterface $order
     * @return SubscriptionInterface
     */
    public function saveSubscription(
        SubscriptionInterface $subscription,
        OrderInterface $order
    ): SubscriptionInterface {
        if (!$order->getIsVirtual()) {
            /** @var AddressInterface $address */
            $address = $this->addressFactory->create();
            $address->setData($order->getShippingAddress()->getData());
            $address->setEntityId(null);
            $address->setSubscriptionId($subscription->getSubscriptionId());
            $this->addressRepository->save($address);
            $subscription->setAddressId($address->getEntityId());
        }

        $this->subscriptionRepository->save($subscription);
        $subscription = $this->subscriptionRepository->getById($subscription->getId());

        return $subscription;
    }

    /**
     * @param SubscriptionPlanInterface $plan
     * @return int|null
     */
    public function calculateActualCouponUsages(SubscriptionPlanInterface $plan)
    {
        $maxUsages = $plan->getEnableDiscount()
            && $plan->getEnableDiscountLimit()
            && $plan->getNumberOfDiscountCycles()
            ? $plan->getNumberOfDiscountCycles()
            : null;

        $isTrialEnabled = $plan->getEnableTrial() && $plan->getTrialDays();
        if ($maxUsages && !$isTrialEnabled) {
            $maxUsages--;
        }

        return $maxUsages;
    }

    /**
     * @param CartItemInterface $item
     * @return array
     */
    private function getItemOptions(CartItemInterface $item): array
    {
        /** @var MagentoProduct $product */
        $product = $item->getProduct();
        /** @var DataObject $request */
        $request = $item->getBuyRequest();

        $options = [];
        if ($typeOptions = self::TYPE_OPTIONS[$product->getTypeId()] ?? null) {
            $options = array_intersect_key($request->getData(), array_flip($typeOptions));
        }

        $customOptions = $request->getData('options');
        $customOptions && $options['options'] = $customOptions;

        return $options;
    }
}
