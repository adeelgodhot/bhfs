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
use Magento\Catalog\Model\ImageUploader;

class SaveImages implements DataPreprocessorInterface
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    public function __construct(
        ImageUploader $imageUploader
    ) {
        $this->imageUploader = $imageUploader;
    }

    public function process(array $data): array
    {
        foreach (ConvertCatalogPartsImages::PARTS_PREFIXES as $partPrefix) {
            $imageKey = "{$partPrefix}_" . LabelFrontendSettingsInterface::IMAGE;

            if (!empty($data[$imageKey][0]['tmp_name']) && !empty($data[$imageKey][0]['name'])) {
                $name = $data[$imageKey][0]['name'];
                $this->imageUploader->moveFileFromTmp($name, true);
                $data[$imageKey] = $name;
            } elseif (!empty($data[$imageKey][0]['name'])) {
                $name = $data[$imageKey][0]['name'];
                $data[$imageKey] = $name;
            } else {
                $data[$imageKey] = null;
            }
        }

        return $data;
    }
}
