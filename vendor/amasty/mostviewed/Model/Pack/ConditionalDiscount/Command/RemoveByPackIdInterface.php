<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command;

use Magento\Framework\Exception\CouldNotDeleteException;

interface RemoveByPackIdInterface
{
    /**
     * @param int $packId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(int $packId): bool;
}
