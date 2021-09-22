<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\Preprocessors\MergeCssConfigs;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class MergeCssConfigsTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\MergeCssConfigs
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class MergeCssConfigsTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers \Amasty\Label\Model\Label\Save\Preprocessors\MergeCssConfigs::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     */
    public function testProcess(array $data, array $testResult): void
    {
        $processor = $this->getObjectManager()->getObject(
            MergeCssConfigs::class
        );

        $this->assertEquals(
            $testResult,
            $processor->process($data)
        );
    }

    public function getProcessorDataProvider(): array
    {
        return [
            [
                [
                    'category_color' => '#111',
                    'product_color' => '#222',
                    'category_size' => '12rem',
                    'product_size' => '11rem'
                ],
                [
                    'category_style' => 'font-size: 12rem; color: #111;',
                    'product_style' => 'font-size: 11rem; color: #222;'
                ]
            ]
        ];
    }
}
