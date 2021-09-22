<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Api\WebHook;

interface HandlerInterface
{
    /**
     * @param array $payload
     * @return void
     */
    public function process(array $payload);
}
