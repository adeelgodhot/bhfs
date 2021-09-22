<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Api\WebHook;

use Magento\Framework\App\RequestInterface;

interface ProcessorInterface
{
    /**
     * @param RequestInterface $request
     * @return void
     */
    public function processRequest(RequestInterface $request);
}
