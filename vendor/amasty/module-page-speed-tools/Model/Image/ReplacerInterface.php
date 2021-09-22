<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */


namespace Amasty\PageSpeedTools\Model\Image;

interface ReplacerInterface
{
    public function execute(string $image, string $imagePath): string;
}
