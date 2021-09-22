<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Api;

interface IpnHandlerInterface
{
    /**
     * Get ipn data, run corresponding handler
     *
     * @param \Stripe\Event $event
     * @return void
     * @throws \Exception
     */
    public function process(\Stripe\Event $event);
}
