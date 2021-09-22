<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Model\Product\Review;

class ReviewSummary extends \Amasty\Amp\Model\Di\Wrapper
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        $name = ''
    ) {
        parent::__construct($objectManagerInterface, \Magento\Review\Model\ReviewSummary::class);
    }
}
