<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\Checkout\Orderline;

use Amasty\RecurringPayments\Model\Checkout\Subscription\Fee;
use Klarna\Core\Model\Checkout\Orderline\OrderLineFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class OrderLineFactoryPlugin
 */
class OrderLineFactoryPlugin
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param OrderLineFactory $subject
     * @param callable $proceed
     * @param string $className
     *
     * @return mixed
     */
    public function aroundCreate(OrderLineFactory $subject, callable $proceed, string $className)
    {
        if ($className === Fee::class) {
            return $this->objectManager->get($className);
        }

        return $proceed($className);
    }
}
