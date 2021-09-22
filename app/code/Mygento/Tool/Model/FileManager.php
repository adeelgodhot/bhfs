<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mygento\Tool\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as Request;

/**
 * A service for handling RequireJS files in the application
 */
class FileManager
{

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\Filesystem $appFilesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem $appFilesystem,
        Request $request
    ) {
        $this->filesystem = $appFilesystem;
        $this->request = $request;
    }

    public function touchLayoutFile($content){
        $fullActionName = $this->request->getFullActionName();

        $layoutDir = "analysis/templates/layout/{$fullActionName}.html";
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
        $dir->writeFile($layoutDir, $content);
    }
    public function touchContentFile($content){
        $fullActionName = $this->request->getFullActionName();

        $contentDir = "analysis/templates/layout/{$fullActionName}_content.html";
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::PUB);
        $dir->writeFile($contentDir, $content);
    }
}
