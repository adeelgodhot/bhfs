<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers\Invoice;

use Amasty\RecurringPayments\Model\Config\Source\Status;

class PaymentActionRequired extends AbstractInvoice
{
    /**
     * @param \Stripe\Event $event
     */
    public function process(\Stripe\Event $event)
    {
        $subscription = $this->getSubscription($event);

        if (!$subscription) {
            return;
        }

        $storeId = (int)$subscription->getStoreId();
        $templateVariables = [
            'linkauth' => $event->data->object->hosted_invoice_url
        ];
        $template = $this->config->getEmailTemplateForeAuthenticate($storeId);
        $this->emailNotifier->sendEmail(
            $subscription,
            $template,
            $templateVariables
        );
        $this->saveTransactionLog($event, Status::ACTION_REQUIRED);
    }
}
