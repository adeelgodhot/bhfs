<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\Preprocessors\ProcessShapeLabelType;
use Amasty\Label\Model\Label\Shape\GenerateImageFromShape;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ProcessShapeLabelTypeTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\ProcessShapeLabelType
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProcessShapeLabelTypeTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers       \Amasty\Label\Model\Label\Save\Preprocessors\ProcessShapeLabelType::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testProcess(array $data, array $expected): void
    {
        $shapeGeneratorMock = $this->createMock(GenerateImageFromShape::class);
        $shapeGeneratorMock
            ->expects($this->any())
            ->method('execute')
            ->willReturn('test.svg');
        $processor = $this->getObjectManager()->getObject(
            ProcessShapeLabelType::class,
            ['generateImageFromShape' => $shapeGeneratorMock]
        );

        $this->assertEquals(
            $expected,
            $processor->process($data)
        );
    }

    public function getProcessorDataProvider(): array
    {
        return [
            [
                [
                    'category_label_type' => 1,
                    'category_label_shape' => 'eee',
                    'category_image' => 'eew',
                    'category_label_shape_color' => '#ddd',
                    'product_label_type' => 1,
                    'product_label_shape' => 'eee',
                    'product_image' => 'dwq',
                    'product_label_shape_color' => '#ddd',
                ],
                [
                    'category_image' => 'test.svg',
                    'product_image' => 'test.svg',
                ]
            ]
        ];
    }
}
