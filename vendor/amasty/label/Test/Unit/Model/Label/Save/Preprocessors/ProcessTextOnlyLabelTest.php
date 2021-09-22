<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\Preprocessors\ProcessTextOnlyLabel;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ProcessTextOnlyLabelTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\ProcessTextOnlyLabel
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProcessTextOnlyLabelTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers       \Amasty\Label\Model\Label\Save\Preprocessors\ProcessTextOnlyLabel::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testProcess(array $data, array $expected): void
    {
        $processor = $this->getObjectManager()->getObject(
            ProcessTextOnlyLabel::class
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
                    'category_label_type' => 0,
                    'category_label_shape' => 'eee',
                    'category_image' => 'eew',
                    'category_label_shape_color' => '#ddd',
                    'product_label_type' => 0,
                    'product_label_shape' => 'eee',
                    'product_image' => 'dwq',
                    'product_label_shape_color' => '#ddd',
                ],
                [
                    'product_image' => null,
                    'category_image' => null,
                ]
            ]
        ];
    }
}
