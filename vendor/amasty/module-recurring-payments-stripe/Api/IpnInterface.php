<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Api;

interface IpnInterface
{
    /**
     * Get ipn data, run corresponding handler
     *
     * @return void
     * @throws \Exception
     */
    public function processIpnRequest(\Stripe\Event $event);
}
