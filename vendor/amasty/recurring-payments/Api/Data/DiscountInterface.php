<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

/**
 * @deprecated use SubscriptionInterface
 */
interface DiscountInterface
{
    const ENTITY_ID = 'entity_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const DISCOUNT_USAGE = 'discount_usage';
    const AVAILABLE_DISCOUNT_USAGE = 'available_discount_usage';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getSubscriptionId();

    /**
     * @param string $subscriptionId
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * @return int
     */
    public function getDiscountUsage();

    /**
     * @param int $discountUsage
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     */
    public function setDiscountUsage($discountUsage);

    /**
     * @return int|null
     */
    public function getAvailableDiscountUsage();

    /**
     * @param int|null $availableDiscountUsage
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     */
    public function setAvailableDiscountUsage($availableDiscountUsage);
}
