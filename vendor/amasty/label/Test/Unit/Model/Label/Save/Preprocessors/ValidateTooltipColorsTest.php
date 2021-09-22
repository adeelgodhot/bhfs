<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\Preprocessors\ValidateTooltipColors;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Class ValidateTooltipColorsTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\ValidateTooltipColors
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ValidateTooltipColorsTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers \Amasty\Label\Model\Label\Save\Preprocessors\ValidateTooltipColors::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     */
    public function testProcess(array $data): void
    {
        $this->expectException(ValidationException::class);
        $processor = $this->getObjectManager()->getObject(
            ValidateTooltipColors::class
        );
        $processor->process($data);
    }

    public function getProcessorDataProvider(): array
    {
        return [
            [
                [
                    'label_tooltip_text_color' => 'lkjk',
                    'label_tooltip_color' => 'kddd'
                ]
            ],
            [
                [
                    'label_tooltip_text_color' => '#564564654',
                    'label_tooltip_color' => '#11'
                ]
            ],
            [
                [
                    'label_tooltip_text_color' => '#2222',
                    'label_tooltip_color' => '#2222'
                ]
            ],
        ];
    }
}
