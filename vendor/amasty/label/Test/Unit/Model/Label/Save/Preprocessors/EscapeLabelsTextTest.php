<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Model\Label\Save\Preprocessors\EscapeLabelsText;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;

/**
 * Class EscapeLabelsTextTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\EscapeLabelsText
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EscapeLabelsTextTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers \Amasty\Label\Model\Label\Save\Preprocessors\EscapeLabelsText::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testProcess(array $data, array $expected): void
    {
        $escaperMock = $this->createMock(Escaper::class);
        $escaperMock
            ->expects($this->any())
            ->method('escapeHtml')
            ->willReturn('&lt;script&gt;alert(&quot;lol&quot;)&lt;/script&gt;');
        $processor = $this->getObjectManager()->getObject(
            EscapeLabelsText::class,
            ['escaper' => $escaperMock]
        );
        $this->assertEquals(
            $processor->process($data),
            $expected
        );
    }

    public function getProcessorDataProvider(): array
    {
        return [
            [
                [
                    'product_label_text' => '<script>alert("lol")</script>',
                    'category_label_text' => '<script>alert("lol")</script>',
                    'name' => '<script>alert("lol")</script>',
                ],
                [
                    'product_label_text' => '&lt;script&gt;alert(&quot;lol&quot;)&lt;/script&gt;',
                    'category_label_text' => '&lt;script&gt;alert(&quot;lol&quot;)&lt;/script&gt;',
                    'name' => '&lt;script&gt;alert(&quot;lol&quot;)&lt;/script&gt;'
                ]
            ],
        ];
    }
}
