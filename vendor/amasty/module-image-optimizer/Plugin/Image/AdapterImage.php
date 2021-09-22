<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Plugin\Image;

use Amasty\ImageOptimizer\Plugin\AutomaticallyProcessImage;

class AdapterImage
{
    /**
     * @var string
     */
    private $image;

    /**
     * @var AutomaticallyProcessImage
     */
    private $automaticallyProcessImage;

    public function __construct(AutomaticallyProcessImage $automaticallyProcessImage)
    {
        $this->automaticallyProcessImage = $automaticallyProcessImage;
    }

    /**
     * @param $subject
     * @param $path
     * @param $newFileName
     */
    public function beforeSave($subject, $path = null, $newFileName = null)
    {
        if ($path !== null) {
            if ($newFileName !== null) {
                $this->image = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newFileName;
            } else {
                $this->image = $path;
            }
        } else {
            $this->image = false;
        }
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterSave($subject, $result)
    {
        if ($this->image) {
            $this->automaticallyProcessImage->execute($this->image);
        }

        return $result;
    }
}
