<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Plugin\Framework\File;

use Amasty\ImageOptimizer\Plugin\AutomaticallyProcessImage;
use Magento\Framework\File\Uploader;

class UploaderPlugin
{
    const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/gif',
        'image/png',
    ];

    /**
     * @var AutomaticallyProcessImage
     */
    private $automaticallyProcessImage;

    public function __construct(AutomaticallyProcessImage $automaticallyProcessImage)
    {
        $this->automaticallyProcessImage = $automaticallyProcessImage;
    }

    /**
     * @param Uploader $subject
     * @param bool|array $result
     * @return mixed
     */
    public function afterSave(Uploader $subject, $result)
    {
        if (!isset($result['path'])) {
            return $result;
        }

        if ($this->isImageMimeTypeAllowed($result['type'] ?? '')) {
            $this->automaticallyProcessImage->execute($result['path'] . DIRECTORY_SEPARATOR . $result['file']);
        }

        return $result;
    }

    private function isImageMimeTypeAllowed(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES);
    }
}
