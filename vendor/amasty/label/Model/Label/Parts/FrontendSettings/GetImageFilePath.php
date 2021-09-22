<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Parts\FrontendSettings;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Driver\File;

class GetImageFilePath
{
    const AMASTY_LABEL_MEDIA_PATH = 'amasty/amlabel';
    const AMASTY_LABEL_TMP_MEDIA_PATH = 'amasty/tmp/amlabel';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var DriverInterface
     */
    private $driver;

    public function __construct(
        Filesystem $filesystem,
        File $driver
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
    }

    public function execute(?string $imageName, $checkExists = true): ?string
    {
        $imageFolderPath = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            self::AMASTY_LABEL_MEDIA_PATH
        );
        $labelImagePath = $imageFolderPath . DIRECTORY_SEPARATOR . $imageName;

        if ($checkExists) {
            return $this->driver->isExists($labelImagePath) ? $labelImagePath : null;
        } else {
            return $labelImagePath;
        }
    }
}
