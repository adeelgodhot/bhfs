<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers\Invoice;

use Amasty\RecurringPayments\Model\Config\Source\Status;

class PaymentFailed extends AbstractInvoice
{
    /**
     * @inheritDoc
     */
    public function process(\Stripe\Event $event)
    {
        $this->saveTransactionLog($event, Status::FAILED);
    }
}
