<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Api;

use Amasty\Stripe\Api\CustomerRepositoryInterface as StripeCustomerRepository;
use Amasty\Stripe\Api\Data\CustomerInterface;

/**
 * @api
 */
interface CustomerRepositoryInterface extends StripeCustomerRepository
{
    /**
     * @param string $stripeId
     *
     * @return CustomerInterface
     */
    public function getByStripeId(string $stripeId): CustomerInterface;
}
