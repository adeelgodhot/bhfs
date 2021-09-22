<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder;

class CompositeHandler
{
    /**
     * @var HandlerPartsOrderedList
     */
    private $handlerPartsOrderedList;

    public function __construct(HandlerPartsOrderedList $handlerPartsOrderedList)
    {
        $this->handlerPartsOrderedList = $handlerPartsOrderedList;
    }

    /**
     * @param HandlerPartInterface $handler
     * @param string $key
     * @param string $after
     */
    public function addPart(HandlerPartInterface $handler, string $key, string $after): void
    {
        $this->handlerPartsOrderedList->addPart($handler, $key, $after);
    }

    /**
     * @param HandleOrderContext $context
     */
    public function handle(HandleOrderContext $context): void
    {
        foreach ($this->handlerPartsOrderedList->getHandlerParts() as $handlerPart) {
            $handlerPart->validate($context);
            $success = $handlerPart->handlePartial($context);

            if (!$success) {
                break;
            }
        }
    }
}
