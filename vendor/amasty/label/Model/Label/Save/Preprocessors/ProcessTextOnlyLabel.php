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
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\ConvertCatalogPartsImages;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\SelectTypeTab;

class ProcessTextOnlyLabel implements DataPreprocessorInterface
{
    public function process(array $data): array
    {
        foreach (ConvertCatalogPartsImages::PARTS_PREFIXES as $partPrefix) {
            $labelTypeKey = "{$partPrefix}_label_type";

            if (array_key_exists($labelTypeKey, $data)
                && SelectTypeTab::TEXT_ONLY === (int) $data[$labelTypeKey]
            ) {
                $shapeCodeKey = "{$partPrefix}_label_shape";
                $imageKey = "{$partPrefix}_" . LabelFrontendSettingsInterface::IMAGE;
                $shapeColorKey = "{$partPrefix}_label_shape_color";
                unset($data[$labelTypeKey], $data[$shapeCodeKey], $data[$shapeColorKey], $data[$imageKey]);
                $data[$imageKey] = null;
            }
        }

        return $data;
    }
}
