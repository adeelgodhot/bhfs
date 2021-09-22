<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;
use Amasty\RecurringPayments\Model\Payment;

class SupportedPayments extends AbstractArray
{
    /**
     * @var Payment
     */
    private $paymentConfig;

    public function __construct(
        Payment $paymentConfig
    ) {
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->paymentConfig->getConfigMethods();
    }
}
