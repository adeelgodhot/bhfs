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

class MergeCssConfigs implements DataPreprocessorInterface
{
    public function process(array $data): array
    {
        foreach (ConvertCatalogPartsImages::PARTS_PREFIXES as $partPrefix) {
            $colorStyleKey = "{$partPrefix}_color";
            $additionalCssKey = "{$partPrefix}_" . LabelFrontendSettingsInterface::STYLE;
            $additionalCss = $data[$additionalCssKey] ?? '';
            $textSizeCssKey = "{$partPrefix}_size";

            if (!empty($data[$textSizeCssKey])) {
                $additionalCss = preg_replace('/font-size\s*?:\s*?[\sa-z\d]+;/', '', $additionalCss);
                $additionalCss .= sprintf(' font-size: %s;', $data[$textSizeCssKey]);
            }

            if (!empty($data[$colorStyleKey])) {
                $additionalCss = preg_replace(
                    '/color\s*?:\s*?[\s]*#[\da-fA-F]{3,6};/',
                    '',
                    $additionalCss
                );
                $additionalCss .= sprintf(' color: %s;', $data[$colorStyleKey]);
            }

            unset($data[$textSizeCssKey], $data[$colorStyleKey]);
            $additionalCss = preg_replace('/\s+/', ' ', $additionalCss);
            $data[$additionalCssKey] = trim($additionalCss);
        }

        return $data;
    }
}
