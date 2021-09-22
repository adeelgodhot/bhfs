<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Helper;

class Deploy extends \Amasty\Base\Helper\Deploy
{
    /**
     * @param $filePath
     * @param $fromPath
     * @param $toPath
     *
     * @return mixed
     */
    protected function getNewFilePath($filePath, $fromPath, $toPath)
    {
        $newFileName = str_replace($fromPath, $toPath, $filePath);
        if ($this->rootRead->isExist($newFileName) && $this->rootRead->isFile($filePath)) {
            $this->rootWrite->delete($newFileName);
        }

        return $newFileName;
    }
}
