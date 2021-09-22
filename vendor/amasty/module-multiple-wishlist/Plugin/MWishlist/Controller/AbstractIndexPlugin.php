<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\MWishlist\Controller;

use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Amasty\MWishlist\Model\ConfigProvider;
use Magento\Framework\Exception\NotFoundException;

class AbstractIndexPlugin
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
     * @param AbstractIndexInterface $controller
     * @throws NotFoundException
     */
    public function beforeExecute(AbstractIndexInterface $controller): void
    {
        if (!$this->configProvider->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
