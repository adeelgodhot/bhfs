<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */


namespace Amasty\ImageOptimizer\Model\Image\Directory;

interface FileSelectorInterface
{
    public function selectFiles(array $files, string $imageDirectory): array;
}
