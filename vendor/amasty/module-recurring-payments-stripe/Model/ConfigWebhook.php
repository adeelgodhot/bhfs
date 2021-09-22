<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\Stripe\Gateway\Config\Config;

class ConfigWebhook extends Config
{
    const WEBHOOK_SECRET = 'webhook_secret';

    /**
     * @return string
     */
    public function getWebhookSecret(): string
    {
        return $this->encryptor->decrypt($this->getValue(self::WEBHOOK_SECRET));
    }
}
