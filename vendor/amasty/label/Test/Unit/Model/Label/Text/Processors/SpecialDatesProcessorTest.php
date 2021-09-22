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
use Amasty\Label\Model\Label\Text\Processors\SpecialDatesProcessor;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class SpecialDatesProcessorTest
 *
 * @see \Amasty\Label\Model\Label\Text\Processors\SpecialDatesProcessor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class SpecialDatesProcessorTest extends \PHPUnit\Framework\TestCase
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
        $dateTime = $this->createMock(DateTime::class);
        $dateTime
            ->expects($this->any())
            ->method('date')
            ->willReturn('04-06-2020');
        $this->processor = $this->getObjectManager()->getObject(
            SpecialDatesProcessor::class,
            ['dateTime' => $dateTime]
        );
        $this->label = $this->getMockForAbstractClass(LabelInterface::class);
        $this->product = $this->createMock(Product::class);
        $this->product
            ->expects($this->any())
            ->method('getSpecialToDate')
            ->willReturn('05-06-2020');
    }

    /**
     * @covers       \Amasty\Label\Model\Label\Text\Processors\SpecialDatesProcessor::process
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
     * @covers       \Amasty\Label\Model\Label\Text\Processors\SpecialDatesProcessor::process
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
     * @covers       \Amasty\Label\Model\Label\Text\Processors\SpecialDatesProcessor::process
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
                SpecialDatesProcessor::HOUR_LEFT_FOR_SPECIAL_PRICE,
                '24'
            ],
            [
                SpecialDatesProcessor::DAYS_LEFT_FOR_SPECIAL_PRICE,
                '1'
            ]
        ];
    }

    public function possibleVariablesProvider(): array
    {
        return [
            [
                [
                    SpecialDatesProcessor::DAYS_LEFT_FOR_SPECIAL_PRICE,
                    SpecialDatesProcessor::HOUR_LEFT_FOR_SPECIAL_PRICE,
                ]
            ]
        ];
    }
}
