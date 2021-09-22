<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Api\Config;

interface ValidatorInterface
{
    /**
     * @return \Generator Error messages
     */
    public function enumerateConfigurationIssues(): \Generator;
}
