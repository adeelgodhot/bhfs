<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder;

interface HandlerPartInterface
{
    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool;

    /**
     * @param HandleOrderContext $context
     * @throws \InvalidArgumentException
     */
    public function validate(HandleOrderContext $context):void;
}
