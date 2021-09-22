<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Controller\Adminhtml\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;

class ClearFolder
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function execute($folder)
    {
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($mediaDirectory->isDirectory($folder)) {
            foreach ($mediaDirectory->read($folder) as $item) {
                try {
                    $mediaDirectory->delete($item);
                } catch (\Exception $e) {
                    throw new LocalizedException(__('Couldn\'t clear `%1` folder', $folder));
                }
            }
        }
    }
}
