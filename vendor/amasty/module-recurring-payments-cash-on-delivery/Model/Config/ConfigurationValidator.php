<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringCashOnDelivery
 */


namespace Amasty\RecurringCashOnDelivery\Model\Config;

use Amasty\RecurringPayments\Api\Config\ValidatorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigurationValidator implements ValidatorInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function enumerateConfigurationIssues(): \Generator
    {
        if (!$this->scopeConfig->isSetFlag('payment/cashondelivery/active')) {
            yield __('Cash On Delivery payment method is not enabled');
        }
    }
}
