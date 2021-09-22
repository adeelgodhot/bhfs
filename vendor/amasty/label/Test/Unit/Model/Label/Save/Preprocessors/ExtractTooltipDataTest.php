<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Parts\LabelTooltip;
use Amasty\Label\Model\Label\Save\Preprocessors\ExtractTooltipData;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ExtractTooltipDataTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\ExtractTooltipData
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ExtractTooltipDataTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers \Amasty\Label\Model\Label\Save\Preprocessors\ExtractTooltipData::process
     * @dataProvider getDataForProcess
     *
     * @param array $data
     * @param array $testResult
     */
    public function testProcess(array $data, array $testResult): void
    {
        $processor = $this->getObjectManager()->getObject(
            ExtractTooltipData::class
        );

        $this->assertEquals(
          $testResult,
          $processor->process($data)
        );
    }

    public function getDataForProcess(): array
    {
        return [
            [
                [
                    'label_tooltip_color' => '#111',
                    'label_tooltip_text_color' => '#111',
                    'label_tooltip_text' => 'dqwwdwdwqdwqdqw',
                    'label_tooltip_status' => 1,
                ],
                [
                    LabelTooltip::EXTENSION_ATTRIBUTES_KEY => [
                        LabelTooltip::PART_CODE => [
                            'status' => 1,
                            'color' => '#111',
                            'text_color' => '#111',
                            'text' => 'dqwwdwdwqdwqdqw'
                        ]
                    ]
                ]
            ]
        ];
    }
}
