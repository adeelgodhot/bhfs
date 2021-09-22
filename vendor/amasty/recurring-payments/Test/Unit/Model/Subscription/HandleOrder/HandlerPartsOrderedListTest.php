<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Test\Unit\Model\Subscription\HandleOrder;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartsOrderedList;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see HandlerPartsOrderedList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class HandlerPartsOrderedListTest extends TestCase
{
    /**
     * @var HandlerPartsOrderedList
     */
    private $model;

    /**
     * @var HandlerPartInterface[]
     */
    private $handlers = [];

    public function setUp(): void
    {
        $handler1 = $this->createMock(HandlerPartInterface::class);
        $handler2 = $this->createMock(HandlerPartInterface::class);
        $handler3 = $this->createMock(HandlerPartInterface::class);

        $this->handlers = [
            'handler1' => $handler1,
            'handler2' => $handler2,
            'handler3' => $handler3,
        ];

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(HandlerPartsOrderedList::class, [
            'handlerParts' => $this->handlers
        ]);
    }

    /**
     * @covers HandlerPartsOrderedList::getHandlerParts
     */
    public function testGetHandlerParts()
    {
        $this->assertEquals($this->handlers, $this->model->getHandlerParts());
    }

    /**
     * @covers HandlerPartsOrderedList::addPart
     */
    public function testAddPart()
    {
        $handlers = $this->handlers;

        $handler4 = $this->createMock(HandlerPartInterface::class);
        $handler5 = $this->createMock(HandlerPartInterface::class);
        $handler6 = $this->createMock(HandlerPartInterface::class);
        $handler7 = $this->createMock(HandlerPartInterface::class);

        $handlers = [
            'handler4' => $handler4,
            'handler1' => $this->handlers['handler1'],
            'handler7' => $handler7,
            'handler2' => $this->handlers['handler2'],
            'handler3' => $this->handlers['handler3'],
            'handler6' => $handler6,
            'handler5' => $handler5,
        ];

        $this->model->addPart(
            $handler4,
            'handler4',
            HandlerPartsOrderedList::BEFORE_ALL
        );

        $this->model->addPart(
            $handler6,
            'handler6',
            HandlerPartsOrderedList::AFTER_ALL
        );

        $this->model->addPart(
            $handler5,
            'handler5',
            HandlerPartsOrderedList::AFTER_ALL
        );

        $this->model->addPart(
            $handler7,
            'handler7',
            'handler1'
        );

        $this->assertEquals($handlers, $this->model->getHandlerParts());
    }
}
