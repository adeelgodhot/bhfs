<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\Finder;

class GetItemId
{
    /**
     * @var int
     */
    private $lastId = -100;

    public function execute(): int
    {
        return ++$this->lastId;
    }
}
