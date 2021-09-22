<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var ConditionalDiscountInterfaceFactory
     */
    private $conditionalDiscountFactory;

    public function __construct(ConditionalDiscountInterfaceFactory $conditionalDiscountFactory)
    {
        $this->conditionalDiscountFactory = $conditionalDiscountFactory;
    }

    public function execute(): ConditionalDiscountInterface
    {
        return $this->conditionalDiscountFactory->create();
    }
}
