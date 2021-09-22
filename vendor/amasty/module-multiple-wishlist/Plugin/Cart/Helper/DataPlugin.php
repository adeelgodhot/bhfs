<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\Cart\Helper;

use Amasty\Cart\Helper\Data;
use Amasty\MWishlist\Model\ConfigProvider;

class DataPlugin
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @param Data $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsWishlistAjax(Data $subject, bool $result): bool
    {
        if ($this->configProvider->isEnabled()) {
            $result = false;
        }

        return $result;
    }
}
