<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizerUi
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizerUi\Ui\DataProvider\Image;

use Amasty\ImageOptimizerUi\Api\ImageSettingRepositoryInterface;
use Amasty\ImageOptimizerUi\Model\Image\ImageSetting;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    const IMAGE_SETTING_ID = 'image_setting_id';
    const OPTIMIZE = 'image_setting_optimize';
    const IMAGE_SETTING_DATA = 'image_setting_data';

    /**
     * @var ImageSettingRepositoryInterface
     */
    private $repository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        ImageSettingRepositoryInterface $repository,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        UrlInterface $url,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->repository = $repository;
        $this->dataPersistor = $dataPersistor;
        $this->collection = $this->repository->getImageSettingCollection();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->url = $url;
    }

    public function getData(): array
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        $this->loadedData = [];
        $data = parent::getData();
        if (isset($data['items'][0])) {
            $imageSettingId = (int)$data['items'][0][ImageSetting::IMAGE_SETTING_ID];
            $imageSetting = $this->repository->getById($imageSettingId);
            $this->loadedData[$imageSettingId] = $imageSetting->getData();
            $this->loadedData[$imageSettingId][ImageSetting::FOLDERS] = $imageSetting->getFolders();
        }
        $data = $this->dataPersistor->get(self::IMAGE_SETTING_DATA);

        if (!empty($data)) {
            $imageSettingId = isset($data[ImageSetting::IMAGE_SETTING_ID])
                ? $data[ImageSetting::IMAGE_SETTING_ID]
                : null;
            $this->loadedData[$imageSettingId] = $data;
            $this->dataPersistor->clear(self::IMAGE_SETTING_DATA);
        }

        return $this->loadedData;
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $imageSettingId = $this->request->getParam(self::IMAGE_SETTING_ID);
        if ($this->dataPersistor->get(self::OPTIMIZE) && $imageSettingId) {
            $meta['modal']['children']['optimization']['arguments']['data']['config'] = [
                'forceStart' => 1,
                'startUrl' => $this->url->getUrl(
                    'amimageoptimizer/image/start',
                    [self::IMAGE_SETTING_ID => $imageSettingId]
                ),
                'processUrl' => $this->url->getUrl(
                    'amimageoptimizer/image/process',
                    [self::IMAGE_SETTING_ID => $imageSettingId]
                )
            ];
            $this->dataPersistor->clear(self::OPTIMIZE);
        }

        return $meta;
    }
}
