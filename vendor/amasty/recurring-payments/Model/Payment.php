<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Model\Config\ConfigurationValidator;

class Payment
{
    const ZERO_TOTAL = 0;
    const POSITIVE_TOTAL = 1;

    /**
     * @var array enabled by admin
     */
    private $enabledMethods = [];

    /**
     * @var array all methods applicable for subscriptions
     */
    private $supportedMethods = [];

    /**
     * @var array allowed for current context
     */
    private $allowedMethods = [];

    /**
     * @var array method list for admin configuration
     */
    private $configMethods = [];

    /**
     * @var array method list for cron handler (non-self-handled)
     */
    private $cronHandledMethods = [];

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ConfigurationValidator
     */
    private $configurationValidator;

    public function __construct(
        Config $config,
        ConfigurationValidator $configurationValidator
    ) {
        $this->config = $config;
        $this->configurationValidator = $configurationValidator;
    }

    /**
     * Initialize lists of payment methods
     */
    protected function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        $this->enabledMethods = $this->config->getSupportedPayments();
        $allMethods = $this->config->getPaymentsConfig();

        foreach ($allMethods as $code => $method) {
            if ($method['supports_amasty_subscriptions'] ?? false) {
                $isAllowed = in_array($code, $this->enabledMethods)
                    && $this->configurationValidator->isMethodConfiguredProperly($code);

                $this->configMethods[$code] = $method['amasty_subscriptions_display_name'] ?? $method['title'];
                $this->supportedMethods[] = $code;

                $zeroTotalMethod = $method['zero_total_method'] ?? null;
                if ($zeroTotalMethod) {
                    $this->supportedMethods[] = $zeroTotalMethod;
                }

                if ($isAllowed) {
                    $this->allowedMethods[self::POSITIVE_TOTAL] [] = $code;
                    $this->allowedMethods[self::ZERO_TOTAL] [] = $zeroTotalMethod ?? $code;
                }

                if ($method['amasty_subscriptions_cron_handled'] ?? false) {
                    $this->cronHandledMethods[] = $code;
                    $zeroTotalMethod && $this->cronHandledMethods[] = $zeroTotalMethod;
                }
            }
        }

        $this->isInitialized = true;
    }

    /**
     * @param bool $zeroTotal
     * @return array
     */
    public function getAllowedMethods(bool $zeroTotal = false): array
    {
        $this->initialize();

        return $this->allowedMethods[$zeroTotal ? self::ZERO_TOTAL : self::POSITIVE_TOTAL] ?? [];
    }

    /**
     * @return array
     */
    public function getSupportedMethods(): array
    {
        $this->initialize();

        return $this->supportedMethods;
    }

    /**
     * @return array
     */
    public function getConfigMethods(): array
    {
        $this->initialize();

        return $this->configMethods;
    }

    /**
     * @return array
     */
    public function getCronHandledMethods(): array
    {
        $this->initialize();

        return $this->cronHandledMethods;
    }
}
