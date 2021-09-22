<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Api\Data;

/**
 * @api
 */
interface RenderSettingsInterface
{
    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    public function getProduct(): ?\Magento\Catalog\Api\Data\ProductInterface;

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product): void;

    /**
     * @return bool
     */
    public function isLabelVisible(): bool;

    /**
     * @param bool $isVisible
     * @return void
     */
    public function setIsLabelVisible(bool $isVisible): void;
}
