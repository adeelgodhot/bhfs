<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Exceptions\TextRenderException;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Amasty\Label\Model\Label\Text\Processors\StockProcessor;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use PHPUnit\Framework\TestCase;

/**
 * Class StockProcessorTest
 *
 * @see \Amasty\Label\Model\Label\Text\Processors\StockProcessor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class StockProcessorTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var LabelInterface
     */
    private $label;

    public function setUp(): void
    {
        $this->processor = $this->getObjectManager()->getObject(
            StockProcessor::class
        );
        $this->label = $this->getMockForAbstractClass(LabelInterface::class);
        $this->product = $this->createMock(Product::class);
        $this->product
            ->expects($this->any())
            ->method('hasData')
            ->willReturn(true);
        $this->product
            ->expects($this->any())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_SIMPLE);
        $this->product
            ->expects($this->any())
            ->method('getData')
            ->willReturn(1);
    }

    /**
     * @covers       \Amasty\Label\Model\Label\Text\Processors\StockProcessor::process
     * @dataProvider invalidDataProvider
     *
     * @param string $variable
     */
    public function testInvalidVariable(string $variable): void
    {
        $this->expectException(TextRenderException::class);
        $this->processor->getVariableValue($variable, $this->label, $this->product);
    }

    /**
     * @covers       \Amasty\Label\Model\Label\Text\Processors\StockProcessor::process
     * @dataProvider validDataProvider
     *
     * @param string $variable
     * @param string $expected
     */
    public function testGetVariableValue(string $variable, string $expected): void
    {
        $this->assertEquals($expected, $this->processor->getVariableValue(
            $variable,
            $this->label,
            $this->product
        ));
    }

    /**
     * @covers       \Amasty\Label\Model\Label\Text\Processors\StockProcessor::process
     * @dataProvider possibleVariablesProvider
     *
     * @param array $expected
     */
    public function testGetPossibleVariables(array $expected): void
    {
        $this->assertEquals(
            $expected,
            $this->processor->getAcceptableVariables()
        );
    }

    public function invalidDataProvider(): array
    {
        return [
            [
                'test'
            ],
            [
                'dd'
            ]
        ];
    }

    public function validDataProvider(): array
    {
        return [
            [
                StockProcessor::STOCK,
                '1'
            ]
        ];
    }

    public function possibleVariablesProvider(): array
    {
        return [
            [
                [
                    StockProcessor::STOCK
                ]
            ]
        ];
    }
}
