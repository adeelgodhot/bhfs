<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Model\Label\Shape\DataProvider as ShapeDataProcider;
use Magento\Framework\View\Asset\Repository;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddPreviewData implements ModifierInterface
{
    const THUMBNAIL_PATH = 'Amasty_Label::images/example.jpg';

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var ShapeDataProcider
     */
    private $dataProvider;

    public function __construct(
        Repository $assetRepository,
        ShapeDataProcider $dataProvider
    ) {
        $this->assetRepository = $assetRepository;
        $this->dataProvider = $dataProvider;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $configData = [
            'previewImageUrl' => $this->getPreviewImage(),
            'transparentShapes' => $this->dataProvider->getTransparentShapes()
        ];

        foreach (['pdp' => 'product', 'category_page' => 'category'] as $sectionName => $partPrefix) {
            $meta[$sectionName]['children']["{$partPrefix}_label_preview"]
                 ['arguments']['data']['config'] = $configData;
        }

        return $meta;
    }

    private function getPreviewImage(): string
    {
        return $this->assetRepository->getUrl(self::THUMBNAIL_PATH);
    }
}
