<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use Amasty\Label\Model\Label\Shape\GenerateImageFromShape;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\ConvertCatalogPartsImages;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\SelectTypeTab;

class ProcessShapeLabelType implements DataPreprocessorInterface
{
    /**
     * @var GenerateImageFromShape
     */
    private $generateImageFromShape;

    public function __construct(
        GenerateImageFromShape $generateImageFromShape
    ) {
        $this->generateImageFromShape = $generateImageFromShape;
    }

    public function process(array $data): array
    {
        foreach (ConvertCatalogPartsImages::PARTS_PREFIXES as $partPrefix) {
            $labelTypeKey = "{$partPrefix}_label_type";
            $shapeCodeKey = "{$partPrefix}_label_shape";
            $imageKey = "{$partPrefix}_" . LabelFrontendSettingsInterface::IMAGE;
            $shapeColorKey = "{$partPrefix}_label_shape_color";

            if (!empty($data[$labelTypeKey])
                && SelectTypeTab::SHAPE === (int) $data[$labelTypeKey]
                && !empty($data[$shapeCodeKey])
            ) {
                $data[$imageKey] = $this->generateImageFromShape->execute(
                    $data[$shapeCodeKey],
                    $data[$shapeColorKey] ?? null
                );
                unset($data[$labelTypeKey], $data[$shapeCodeKey], $data[$shapeColorKey]);
            }
        }

        return $data;
    }
}
