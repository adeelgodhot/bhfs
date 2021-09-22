<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */


namespace Amasty\ImageOptimizer\Plugin\Image;

use Amasty\ImageOptimizer\Model\Image\ClearGeneratedImageForFile;

class WysiwigImage
{
    /**
     * @var ClearGeneratedImageForFile
     */
    private $clearGeneratedImageForFile;

    public function __construct(ClearGeneratedImageForFile $clearGeneratedImageForFile)
    {
        $this->clearGeneratedImageForFile = $clearGeneratedImageForFile;
    }

    /**
     * @param $subject
     * @param $target
     */
    public function beforeDeleteFile($subject, $target)
    {
        $this->clearGeneratedImageForFile->execute($target);
    }
}
