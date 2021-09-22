<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\RecurringStripe\Api\IpnHandlerInterface;
use Amasty\RecurringStripe\Api\IpnInterface;

class Ipn implements IpnInterface
{
    /**
     * @var IpnHandlerInterface[]
     */
    private $ipnHandlers;

    public function __construct(array $ipnHandlers)
    {
        $this->ipnHandlers = $ipnHandlers;
    }

    /**
     * @param \Stripe\Event $event
     *
     * @throws \Exception
     */
    public function processIpnRequest(\Stripe\Event $event)
    {
        if (!isset($this->ipnHandlers[$event->type])) {
            throw new \RuntimeException(sprintf('Stripe event %1 is not supported', $event->type));
        }

        $this->ipnHandlers[$event->type]->process($event);
    }
}
