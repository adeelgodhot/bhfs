<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\ViewModel\Label\Ui;

use Amasty\Base\Model\Serializer;
use Amasty\Label\Model\ConfigProvider;

class Configuration
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ConfigProvider $configProvider,
        Serializer $serializer
    ) {
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }

    public function getConfig(): string
    {
        return $this->serializer->serialize([
            'scope' => $this->configProvider->getRecentlyViewedScope(),
            'productsLifetime' => $this->configProvider->getRecentlyViewedLifetime()
        ]);
    }
}
