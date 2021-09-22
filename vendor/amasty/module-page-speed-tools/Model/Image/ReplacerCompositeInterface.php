<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_PageSpeedTools
 */


namespace Amasty\PageSpeedTools\Model\Image;

interface ReplacerCompositeInterface
{
    const REPLACE_BEST = 'replace_with_best';
    const REPLACE_PICTURE = 'replace_with_picture';

    public function replace(string $algorithm, string $image, string $imagePath): string;
}
