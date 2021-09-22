<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Test\Unit\Model\Subscription\HandleOrder;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandler;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartsOrderedList;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see CompositeHandler
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CompositeHandlerTest extends TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * @covers CompositeHandler::addPart
     */
    public function testAddPart()
    {
        $handlerPart = $this->createMock(HandlerPartInterface::class);
        $handlerListMock = $this->createMock(HandlerPartsOrderedList::class);
        $handlerListMock->expects($this->once())
            ->method('addPart')
            ->with($handlerPart, 'handler', 'after');

        /** @var CompositeHandler $model */
        $model = $this->objectManager->getObject(
            CompositeHandler::class,
            [
                'handlerPartsOrderedList' => $handlerListMock
            ]
        );

        $model->addPart($handlerPart, 'handler', 'after');
    }

    /**
     * @covers CompositeHandler::handle
     */
    public function testHandle()
    {
        $contextMock = $this->createMock(HandleOrderContext::class);

        $handlerPart1Mock = $this->createMock(HandlerPartInterface::class);
        $handlerPart1Mock->expects($this->once())
            ->method('validate')
            ->with($contextMock);

        $handlerPart1Mock->expects($this->once())
            ->method('handlePartial')
            ->with($contextMock)
            ->willReturn(true);

        $handlerPart2Mock = $this->createMock(HandlerPartInterface::class);
        $handlerPart2Mock->expects($this->once())
            ->method('validate')
            ->with($contextMock);
        $handlerPart2Mock->method('handlePartial')
            ->with($contextMock)
            ->willReturn(false);


        $handlerPart3Mock = $this->createMock(HandlerPartInterface::class);
        $handlerPart3Mock->expects($this->never())
            ->method('validate')
            ->with($contextMock);

        $handlerPart3Mock->expects($this->never())
            ->method('handlePartial')
            ->with($contextMock)
            ->willReturn(true);

        $handlerPartsOrderedListMock = $this->createMock(HandlerPartsOrderedList::class);
        $handlerPartsOrderedListMock->expects($this->once())
            ->method('getHandlerParts')
            ->willReturn([
                'handler1' => $handlerPart1Mock,
                'handler2' => $handlerPart2Mock,
                'handler3' => $handlerPart3Mock,
            ]);

        /** @var CompositeHandler $model */
        $model = $this->objectManager->getObject(
            CompositeHandler::class,
            [
                'handlerPartsOrderedList' => $handlerPartsOrderedListMock
            ]
        );

        $model->handle($contextMock);
    }
}
