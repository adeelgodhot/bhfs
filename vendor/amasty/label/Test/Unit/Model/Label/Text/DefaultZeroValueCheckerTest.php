<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Text;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Text\DefaultZeroValueChecker;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * @see \Amasty\Label\Model\Label\Text\DefaultZeroValueChecker
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DefaultZeroValueCheckerTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var DefaultZeroValueChecker
     */
    private $zeroChecker;

    /**
     * @var LabelInterface
     */
    private $label;

    protected function setUp(): void
    {
        $this->zeroChecker = $this->getObjectManager()->getObject(DefaultZeroValueChecker::class);
        $this->label = $this->getMockForAbstractClass(LabelInterface::class);
    }

    /**
     * @covers \Amasty\Label\Model\Label\Text\DefaultZeroValueChecker::isZeroValue
     * @dataProvider isZeroValueDataProvider
     *
     * @param string $variableValue
     * @param bool $expectedResult
     */
    public function testIsZeroValue(string $variableValue, bool $expectedResult): void
    {
        $this->assertEquals(
            $this->zeroChecker->isZeroValue($variableValue, $this->label),
            $expectedResult
        );
    }

    public function isZeroValueDataProvider(): array
    {
        return [
            [
                '0.00',
                true
            ],
            [
                '0.00 - 0.00',
                true
            ],
            [
                '0',
                true
            ],
            [
                '.00',
                true
            ],
            [
                '0.',
                true
            ],
            [
                '200',
                false
            ],
            [
                '0.00 - 200',
                false
            ],
            [
                '<span>34$</span>',
                false
            ],
            [
                '<span>34.00$</span>',
                false
            ],
            [
                '<span>0.00$</span>',
                true
            ],
            [
                '<span>0.00$ - 12.00$</span>',
                false
            ],
            [
                '0.00 - 12 - 15 - 0.',
                false
            ],
            [
                '<span>0 - 0.00$</span>',
                true
            ],
            [
                '<span>0000-00-00</span>',
                true
            ],
            [
                '0000-00-00',
                true
            ],
        ];
    }
}
