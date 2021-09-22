<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder;

class HandlerPartsOrderedList
{
    const BEFORE_ALL = '-';
    const AFTER_ALL = '+';

    /**
     * @var HandlerPartInterface[]
     */
    private $handlerParts;

    public function __construct(array $handlerParts)
    {
        $this->handlerParts = $handlerParts;
    }

    public function addPart(
        HandlerPartInterface $handlerPart,
        string $key,
        string $modeOrAfterKey = self::AFTER_ALL
    ): void {
        $resultHandlers = [];

        if ($modeOrAfterKey === self::BEFORE_ALL) {
            $resultHandlers[$key] = $handlerPart;
        }

        foreach ($this->handlerParts as $currentKey => $currentHandlerPart) {
            $resultHandlers[$currentKey] = $currentHandlerPart;

            if ($currentKey === $modeOrAfterKey) {
                $resultHandlers[$key] = $handlerPart;
            }
        }

        if ($modeOrAfterKey === self::AFTER_ALL) {
            $resultHandlers[$key] = $handlerPart;
        }

        $this->handlerParts = $resultHandlers;
    }

    /**
     * @return HandlerPartInterface[]
     */
    public function getHandlerParts(): array
    {
        return $this->handlerParts;
    }
}
