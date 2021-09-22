<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;
use Amasty\RecurringPaypal\Model\ConfigProvider;

class ConfigurationValidator implements ValidatorInterface
{
    /**
     * @var ConfigProvider
     */
    private $config;

    public function __construct(
        ConfigProvider $config
    ) {
        $this->config = $config;
    }

    public function enumerateConfigurationIssues(): \Generator
    {
        if (!\class_exists(\PayPal\Rest\ApiContext::class)) {
            yield __('Please install "paypal/rest-api-sdk-php" composer package');
        }

        if (!$this->config->getPaypalCredentials()) {
            yield __('Please fill "Client ID" and "Client Secret" fields and press "Save Config" button');
        }

        if (!$this->config->getPaypalWebhookId()) {
            yield __('Please configure "Webhook ID"');
        }
    }
}
