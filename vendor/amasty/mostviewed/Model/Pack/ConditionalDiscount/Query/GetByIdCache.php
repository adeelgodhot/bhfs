<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByIdCache implements GetByIdInterface
{
    /**
     * @var GetById
     */
    private $getById;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        GetById $getById,
        Registry $registry
    ) {
        $this->getById = $getById;
        $this->registry = $registry;
    }

    /**
     * @param int $id
     * @return ConditionalDiscountInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): ConditionalDiscountInterface
    {
        $conditionalDiscount = $this->registry->get($id);
        if ($conditionalDiscount === null) {
            $conditionalDiscount = $this->getById->execute($id);
            $this->registry->save($conditionalDiscount);
        }

        return $conditionalDiscount;
    }
}
