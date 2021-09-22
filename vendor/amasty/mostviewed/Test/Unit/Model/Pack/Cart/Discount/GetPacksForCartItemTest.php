<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetPacksForCartItem;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;

class GetPacksForCartItemTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var GetPacksForCartItem
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(GetPacksForCartItem::class);
    }

    /**
     * @covers GetPacksForCartItem::isPackCanAppliedForProduct
     *
     * @dataProvider isPackCanAppliedForProductDataProvider
     *
     * @param string $childIds
     * @param array $parentIds
     * @param int $applyForParent
     * @param int $cartProductId
     * @param bool $expectedResult
     * @return void
     * @throws ReflectionException
     */
    public function testIsPackCanAppliedForProduct(
        string $childIds,
        array $parentIds,
        int $applyForParent,
        int $cartProductId,
        bool $expectedResult
    ): void {
        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getParentIds')->willReturn($parentIds);
        $packMock->expects($this->any())->method('getProductIds')->willReturn($childIds);
        $packMock->expects($this->any())->method('getApplyForParent')->willReturn($applyForParent);

        $reflectionModel = new ReflectionObject($this->model);
        $testMethod = $reflectionModel->getMethod('isPackCanAppliedForProduct');
        $testMethod->setAccessible(true);
        $actualResult = $testMethod->invoke($this->model, $packMock, $cartProductId);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function isPackCanAppliedForProductDataProvider(): array
    {
        return [
            [
                '1,2',
                [3, 4],
                0,
                3,
                false
            ],
            [
                '1,2',
                [3, 4],
                1,
                3,
                true
            ],
            [
                '1,2',
                [3, 4],
                0,
                1,
                true
            ],
            [
                '1,2',
                [3, 4],
                1,
                2,
                true
            ]
        ];
    }
}
