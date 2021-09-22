<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Plugin\Model\Tax;

use Magento\Framework\App\RequestInterface;
use Magento\Tax\Model\Config;

class ConfigPlugin
{
    const STRIPE_PATH = '/amasty_recurring/stripe/index/';

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @param Config $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterApplyTaxAfterDiscount(Config $subject, bool $result): bool
    {
        if ($this->request->getPathInfo() === self::STRIPE_PATH) {
            $result = false;
        }

        return $result;
    }
}
