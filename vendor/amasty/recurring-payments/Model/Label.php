<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

/**
 * Class Label
 */
class Label
{
    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getSinglePurchaseLabel(): string
    {
        return $this->config->getSinglePurchaseLabel();
    }

    /**
     * @return string
     */
    public function getSingleRecurringLabel(): string
    {
        return $this->config->getSingleRecurringLabel();
    }
}
