<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Model\LabelRegistry;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class SelectTypeTab implements ModifierInterface
{
    use LabelSpecificSettingsTrait;

    const TEXT_ONLY = 0;
    const IMAGE = 2;
    const SHAPE = 1;

    /**
     * @var LabelRegistry
     */
    private $labelRegistry;

    public function __construct(
        LabelRegistry $labelRegistry
    ) {
        $this->labelRegistry = $labelRegistry;
    }

    protected function executeIfLabelExists(int $labelId, array $data): array
    {
        foreach (ConvertCatalogPartsImages::PARTS_PREFIXES as $partPrefix) {
            $typeKey = "{$partPrefix}_label_type";
            $imageKey = "{$partPrefix}_" . LabelFrontendSettingsInterface::IMAGE;
            $data[$labelId][$typeKey] = empty($data[$labelId][$imageKey]) ? self::TEXT_ONLY : self::IMAGE;
        }

        return $data;
    }
}
