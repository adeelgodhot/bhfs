<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Label\Api\Data\LabelTooltipInterface;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use Magento\Framework\Validation\ValidationException;

class ValidateTooltipColors implements DataPreprocessorInterface
{
    public function process(array $data): array
    {
        foreach ([LabelTooltipInterface::COLOR, LabelTooltipInterface::TEXT_COLOR] as $colorValue) {
            $key = sprintf("%s_%s", LabelTooltipInterface::PART_CODE, $colorValue);

            if (!empty($data[$key]) && !preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $data[$key])) {
                throw new ValidationException(
                    __('Please, recheck tooltip colors settings')
                );
            }
        }

        return $data;
    }
}
