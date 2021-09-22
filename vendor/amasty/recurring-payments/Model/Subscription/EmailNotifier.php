<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription;

use Amasty\RecurringPayments\Api\Subscription\NotificationInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface as MagentoProductRepository;
use Magento\Customer\Api\CustomerRepositoryInterface as MagentoCustomerRepository;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\DataObject as DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class EmailNotifier
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var MagentoCustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var MagentoProductRepository
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    public function __construct(
        ManagerInterface $eventManager,
        Config $config,
        MagentoCustomerRepository $customerRepository,
        CustomerInterfaceFactory $customerFactory,
        MagentoProductRepository $productRepository,
        ProductInterfaceFactory $productFactory,
        TimezoneInterface $timezone,
        DateTimeInterval $dateTimeInterval
    ) {
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->timezone = $timezone;
        $this->dateTimeInterval = $dateTimeInterval;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $template
     * @param array $templateVariables
     */
    public function sendEmail(
        SubscriptionInterface $subscription,
        string $template,
        array $templateVariables = []
    ) {
        $templateVariables = array_merge($this->prepareTemplateVariables($subscription), $templateVariables);
        $storeId = (int)$subscription->getStoreId();

        $data = new DataObject(
            [
                'template'           => $template,
                'store_id'           => $storeId,
                'email_recipient'    => $subscription->getCustomerEmail(),
                'email_sender'       => $this->config->getEmailSender($storeId),
                'template_variables' => $templateVariables
            ]
        );

        $this->eventManager->dispatch('amasty_recurring_send_email', ['email_data' => $data]);
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return array
     */
    private function prepareTemplateVariables(SubscriptionInterface $subscription): array
    {
        try {
            $customer = $this->customerRepository->getById($subscription->getCustomerId());
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerFactory->create();
        }

        try {
            /** @var ProductInterface $product */
            $product = $this->productRepository->getById($subscription->getProductId());
        } catch (NoSuchEntityException $exception) {
            $product = $this->productFactory->create();
        }

        $timezoneName = $subscription->getCustomerTimezone();
        if (!$timezoneName) {
            $timezoneName = 'UTC';
        }
        $startDateObject = new \DateTime($subscription->getStartDate(), new \DateTimeZone('UTC'));
        $startDateObject->setTimezone(new \DateTimeZone($timezoneName));

        $startDate = $this->timezone->formatDate(
            $startDateObject,
            \IntlDateFormatter::MEDIUM
        );

        $templateVariables['customer'] = $customer;
        $templateVariables['product'] = $product;
        $templateVariables['recurring_data'] = [
            NotificationInterface::INTERVAL              => $subscription->getDelivery(),
            NotificationInterface::INITIAL_FEE           => $subscription->getInitialFee(),
            NotificationInterface::REGULAR_PAYMENT       => $subscription->getBaseGrandTotal(),
            NotificationInterface::DISCOUNT_AMOUNT       => $subscription->getBaseDiscountAmount(),
            NotificationInterface::PAYMENT_WITH_DISCOUNT => $subscription->getBaseGrandTotalWithDiscount(),
            NotificationInterface::DISCOUNT_CYCLE        => $subscription->getRemainingDiscountCycles(),
            NotificationInterface::TRIAL_STATUS          => $this->isTrialPeriodActive($subscription) ? 'yes' : 'no',
            NotificationInterface::TRIAL_DAYS            => $subscription->getTrialDays(),
            NotificationInterface::START_DATE            => $startDate
        ];

        return $templateVariables;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    protected function isTrialPeriodActive(SubscriptionInterface $subscription): bool
    {
        return $this->dateTimeInterval->isTrialPeriodActive(
            (string)$subscription->getStartDate(),
            (int)$subscription->getTrialDays()
        );
    }
}
