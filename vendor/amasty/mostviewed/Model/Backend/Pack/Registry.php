<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Backend\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;

class Registry
{
    /**
     * @var PackInterface|null
     */
    private $pack;

    public function set(PackInterface $pack): void
    {
        $this->pack = $pack;
    }

    public function get(): ?PackInterface
    {
        return $this->pack;
    }
}
