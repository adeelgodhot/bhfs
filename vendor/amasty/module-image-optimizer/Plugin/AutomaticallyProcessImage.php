<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Plugin;

use Amasty\ImageOptimizer\Api\Data\ImageSettingInterfaceFactory;
use Amasty\ImageOptimizer\Api\Data\QueueInterface;
use Amasty\ImageOptimizer\Api\Data\QueueInterfaceFactory;
use Amasty\ImageOptimizer\Model\ConfigProvider;
use Amasty\ImageOptimizer\Model\ImageProcessor;
use Amasty\PageSpeedTools\Model\OptionSource\Resolutions;

class AutomaticallyProcessImage
{
    /**
     * @var ImageSettingInterfaceFactory
     */
    private $imageSettingFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        ImageProcessor $imageProcessor,
        ImageSettingInterfaceFactory $imageSettingFactory,
        ConfigProvider $configProvider
    ) {
        $this->imageSettingFactory = $imageSettingFactory;
        $this->configProvider = $configProvider;
        $this->imageProcessor = $imageProcessor;
    }

    public function execute(string $file): void
    {
        if (!$this->configProvider->isAutomaticallyOptimizeImages()) {
            return;
        }

        $imageSetting = $this->imageSettingFactory->create();
        $imageSetting->setJpegTool($this->configProvider->getJpegCommand());
        $imageSetting->setPngTool($this->configProvider->getPngCommand());
        $imageSetting->setGifTool($this->configProvider->getGifCommand());
        $imageSetting->setWebpTool($this->configProvider->getWebpCommand());
        $imageSetting->setIsDumpOriginal($this->configProvider->isDumpOriginal());
        //TODO make resolution as array
        $resolutions = $this->configProvider->getResolutions();
        if (in_array(Resolutions::MOBILE, $resolutions)) {
            $imageSetting->setIsCreateMobileResolution(true);
        }
        if (in_array(Resolutions::TABLET, $resolutions)) {
            $imageSetting->setIsCreateTabletResolution(true);
        }
        $imageSetting->setResizeAlgorithm($this->configProvider->getResizeAlgorithm());

        if ($queueImage = $this->imageProcessor->prepareQueue($file, $imageSetting)) {
            $this->imageProcessor->process($queueImage);
        }
    }
}
