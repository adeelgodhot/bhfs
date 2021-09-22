<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\Store\Model\ScopeInterface;

class Config extends ConfigProviderAbstract
{
    const PATH_PREFIX = 'amasty_recurring_payments/';

    const GENERAL_BLOCK = 'general/';
    const GLOBAL_BLOCK = 'global/';
    const LABEL_COMMENT_BLOCK = 'label_comment/';
    const EMAIL_NOTIFICATION_BLOCK = 'email_notification/';

    const SUPPORTED_PAYMENTS = 'supported_payments';
    const OUT_OF_STOCK_ACTION = 'out_of_stock_action';
    const NUMBER_OF_FAILED_TRANSACTIONS = 'number_of_failed_transactions';
    const ALLOW_SPECIFY_START_END_DATE = 'allow_specify_start_end_date';
    const APPLY_FREE_SHIPPING = 'apply_free_shipping';
    const ENABLE_NEXT_BILLING_DATE_TOOLTIP = 'enable_next_billing_date_tooltip';
    const NEXT_BILLING_DATE_TOOLTIP_TEXT = 'next_billing_date_tooltip_text';

    const SUBSCRIPTION_ONLY = 'subscription_only';
    const SUBSCRIPTION_PLANS = 'subscription_plans';

    const SINGLE_PURCHASE_LABEL = 'single_purchase_label';
    const SINGLE_RECURRING_LABEL = 'single_recurring_label';

    const EMAIL_SENDER = 'email_sender';
    const NOTIFY_SUBSCRIPTION_PURCHASED = 'notify_subscription_purchased';
    const EMAIL_TEMPLATE_SUBSCRIPTION_PURCHASED = 'email_template_subscription_purchased';
    const NOTIFY_TRIAL_END = 'notify_trial_end';
    const EMAIL_TEMPLATE_TRIAL_END = 'email_template_trial_end';
    const EMAIL_TEMPLATE_AUTHENTICATE = 'email_template_authenticate';
    const NOTIFY_SUBSCRIPTION_PAUSED = 'notify_subscription_paused';
    const EMAIL_TEMPLATE_SUBSCRIPTION_PAUSED = 'email_template_subscription_paused';
    const NOTIFY_SUBSCRIPTION_CANCELED = 'notify_subscription_canceled';
    const EMAIL_TEMPLATE_SUBSCRIPTION_CANCELED = 'email_template_subscription_canceled';
    const TRIAL_END_DAYS_BEFORE_NOTIFICATION = 'trial_end_days_before_notification';

    const LOCALE = 'general/locale/code';

    /**
     * @var string xpath prefix of module (section)
     */
    protected $pathPrefix = self::PATH_PREFIX;

    /**
     * @return array
     */
    public function getSupportedPayments(): array
    {
        $ids = $this->getValue(self::GENERAL_BLOCK . self::SUPPORTED_PAYMENTS);

        return $ids ? explode(',', $ids) : [];
    }

    /**
     * @return string
     */
    public function getOutOfStockAction(): string
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::OUT_OF_STOCK_ACTION);
    }

    /**
     * @return int
     */
    public function getNumberOfFailedTransactions(): int
    {
        return (int)$this->getValue(self::GENERAL_BLOCK . self::NUMBER_OF_FAILED_TRANSACTIONS);
    }

    /**
     * @return bool
     */
    public function isAllowSpecifyStartEndDate(): bool
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::ALLOW_SPECIFY_START_END_DATE);
    }

    /**
     * @return bool
     */
    public function isEnableFreeShipping(): bool
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::APPLY_FREE_SHIPPING);
    }

    /**
     * @return bool
     */
    public function isEnabledNextBillingDateTooltip(): bool
    {
        return $this->isSetFlag(self::GENERAL_BLOCK . self::ENABLE_NEXT_BILLING_DATE_TOOLTIP);
    }

    /**
     * @return string
     */
    public function getNextBillingDateTooltipText(): string
    {
        return (string)$this->getValue(self::GENERAL_BLOCK . self::NEXT_BILLING_DATE_TOOLTIP_TEXT);
    }

    /**
     * @return bool
     */
    public function isSubscriptionOnly(): bool
    {
        return $this->isSetFlag(self::GLOBAL_BLOCK . self::SUBSCRIPTION_ONLY);
    }

    /**
     * @return array
     */
    public function getSubscriptionPlansIds(): array
    {
        $value = $this->getValue(self::GLOBAL_BLOCK . self::SUBSCRIPTION_PLANS);
        $value = explode(',', $value);
        $value = array_filter($value);

        return $value;
    }

    /**
     * @return string
     */
    public function getSinglePurchaseLabel(): string
    {
        return (string)$this->getValue(self::LABEL_COMMENT_BLOCK . self::SINGLE_PURCHASE_LABEL);
    }

    /**
     * @return string
     */
    public function getSingleRecurringLabel(): string
    {
        return (string)$this->getValue(self::LABEL_COMMENT_BLOCK . self::SINGLE_RECURRING_LABEL);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailSender(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_SENDER,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function isNotifySubscriptionPurchased(int $storeId): bool
    {
        return $this->isSetFlag(
            self::EMAIL_NOTIFICATION_BLOCK . self::NOTIFY_SUBSCRIPTION_PURCHASED,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailTemplateSubscriptionPurchased(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_SUBSCRIPTION_PURCHASED,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function isNotifyTrialEnd(int $storeId): bool
    {
        return $this->isSetFlag(
            self::EMAIL_NOTIFICATION_BLOCK . self::NOTIFY_TRIAL_END,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailTemplateTrialEnd(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_TRIAL_END,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailTemplateForeAuthenticate(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_AUTHENTICATE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function isNotifySubscriptionPaused(int $storeId): bool
    {
        return $this->isSetFlag(
            self::EMAIL_NOTIFICATION_BLOCK . self::NOTIFY_SUBSCRIPTION_PAUSED,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailTemplateSubscriptionPaused(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_SUBSCRIPTION_PAUSED,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function isNotifySubscriptionCanceled(int $storeId): bool
    {
        return $this->isSetFlag(
            self::EMAIL_NOTIFICATION_BLOCK . self::NOTIFY_SUBSCRIPTION_CANCELED,
            $storeId
        );
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getEmailTemplateSubscriptionCanceled(int $storeId): string
    {
        return (string)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::EMAIL_TEMPLATE_SUBSCRIPTION_CANCELED,
            $storeId
        );
    }

    /**
     * @param string $store
     *
     * @return string
     */
    public function getStoreLocale(string $store): string
    {
        return (string)$this->scopeConfig->getValue(
            self::LOCALE,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }

    public function getPaymentsConfig(): array
    {
        return $this->scopeConfig->getValue('payment');
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getTrialEndDaysBeforeNotification(int $storeId): int
    {
        return (int)$this->getValue(
            self::EMAIL_NOTIFICATION_BLOCK . self::TRIAL_END_DAYS_BEFORE_NOTIFICATION,
            $storeId
        );
    }
}
