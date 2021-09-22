<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Test\Unit\Model\Label\Save\Preprocessors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Save\Preprocessors\FilterActiveFromToDates;
use Amasty\Label\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Label\Test\Unit\Traits\ReflectionTrait;
use Magento\Framework\Stdlib\DateTime\Filter\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Class FilterActiveFromToDatesTest
 *
 * @see \Amasty\Label\Model\Label\Save\Preprocessors\FilterActiveFromToDates
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class FilterActiveFromToDatesTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers \Amasty\Label\Model\Label\Save\Preprocessors\FilterActiveFromToDates::process
     * @dataProvider getProcessorDataProvider
     *
     * @param array $data
     * @param array $expected
     */
    public function testProcess(array $data, array $expected): void
    {
        $dateFilter = $this->createMock(DateTime::class);
        $dateFilter
            ->expects($this->any())
            ->method('filter')
            ->willReturn('Fri, 12 Mar 2021 22:39:13 GMT');

        $processor = $this->getObjectManager()->getObject(
            FilterActiveFromToDates::class,
            ['dateFilter' => $dateFilter]
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
                    LabelInterface::STATUS => true,
                    LabelInterface::ACTIVE_FROM => 'Fri, 12 Mar 2021 22:39:13 GMT',
                    LabelInterface::ACTIVE_TO => 'Fri, 12 Mar 2021 22:39:13 GMT',
                ],
                [
                    LabelInterface::STATUS => false,
                    LabelInterface::ACTIVE_FROM => 'Fri, 12 Mar 2021 22:39:13 GMT',
                    LabelInterface::ACTIVE_TO => 'Fri, 12 Mar 2021 22:39:13 GMT',
                ]
            ]
        ];
    }
}
