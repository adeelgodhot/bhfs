<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Label\Api\Data\LabelTooltipInterface;
use Amasty\Label\Model\Label\Parts\LabelTooltip;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;

class ExtractTooltipData implements DataPreprocessorInterface
{
    public function process(array $data): array
    {
        foreach ($data as $key => $value) {
            if (strpos($key, LabelTooltipInterface::PART_CODE) === 0) {
                $clearKey = str_replace(
                    sprintf('%s_', LabelTooltipInterface::PART_CODE),
                    '',
                    $key
                );

                $data[LabelTooltip::EXTENSION_ATTRIBUTES_KEY][LabelTooltipInterface::PART_CODE][$clearKey] = $value;
                unset($data[$key]);
            }
        }

        return $data;
    }
}
