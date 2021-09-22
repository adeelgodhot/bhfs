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
use Amasty\Label\Model\Label\Text\Processors\NewLineProcessor;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Api\Data\ProductInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class NewLineProcessorTest
 *
 * @see \Amasty\Label\Model\Label\Text\Processors\NewLineProcessor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class NewLineProcessorTest extends TestCase
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
            NewLineProcessor::class
        );
        $this->label = $this->getMockForAbstractClass(LabelInterface::class);
        $this->product = $this->getMockForAbstractClass(ProductInterface::class);
    }

    /**
     * @covers       \Amasty\Label\Model\Label\Text\Processors\NewLineProcessor::process
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
     * @covers       \Amasty\Label\Model\Label\Text\Processors\NewLineProcessor::process
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
     * @covers       \Amasty\Label\Model\Label\Text\Processors\NewLineProcessor::process
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
                'BR',
                '<br/>'
            ]
        ];
    }

    public function possibleVariablesProvider(): array
    {
        return [
          [
              [
                  NewLineProcessor::NEW_LINE
              ]
          ]
        ];
    }
}
