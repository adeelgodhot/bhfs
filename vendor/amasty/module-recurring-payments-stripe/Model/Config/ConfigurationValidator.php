<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;
use Amasty\RecurringStripe\Model\ConfigWebhook;
use Amasty\Stripe\Gateway\Config\Config as GatewayConfig;

class ConfigurationValidator implements ValidatorInterface
{
    /**
     * @var ConfigWebhook
     */
    private $configWebhook;

    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    public function __construct(
        ConfigWebhook $configWebhook,
        GatewayConfig $gatewayConfig
    ) {
        $this->configWebhook = $configWebhook;
        $this->gatewayConfig = $gatewayConfig;
    }

    public function enumerateConfigurationIssues(): \Generator
    {
        if (!class_exists(\Stripe\Stripe::class)) {
            yield __('Please install "stripe/stripe-php" composer package');
        }

        if (!$this->gatewayConfig->isActive()) {
            yield __('Stripe payment method is not enabled');
        }

        if (!$this->gatewayConfig->getPublicKey()) {
            yield __('Stripe payment method is not configured');
        }

        if (!$this->configWebhook->getWebhookSecret()) {
            yield __('Please configure "Webhook Secret"');
        }
    }
}
