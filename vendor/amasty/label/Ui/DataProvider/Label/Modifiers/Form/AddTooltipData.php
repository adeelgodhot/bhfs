<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Ui\DataProvider\Label\Modifiers\Form;

use Amasty\Label\Api\Data\LabelTooltipInterface;
use Amasty\Label\Model\LabelRegistry;
use Magento\Framework\DataObject;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddTooltipData implements ModifierInterface
{
    use LabelSpecificSettingsTrait;

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
        $label = $this->labelRegistry->getCurrentLabel();
        /** @var DataObject $tooltip **/
        $tooltip = $label->getExtensionAttributes()->getLabelTooltip() ?: [];
        $tooltipData = $tooltip ? $tooltip->getData() : [];

        foreach ($tooltipData as $key => $value) {
            $uiFormKey = sprintf('%s_%s', LabelTooltipInterface::PART_CODE, $key);
            $data[$labelId][$uiFormKey] = $value;
        }

        return $data;
    }
}
