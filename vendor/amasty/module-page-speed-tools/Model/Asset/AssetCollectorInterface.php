<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */


namespace Amasty\PageSpeedTools\Model\Asset;

interface AssetCollectorInterface
{
    public function getAssetContentType(): string;

    public function getCollectedAssets(): array;

    public function execute(string $output);
}
