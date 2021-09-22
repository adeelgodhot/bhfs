<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Exceptions\TextRenderException;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class NewLineProcessor implements ProcessorInterface
{
    const NEW_LINE = 'BR';

    public function getAcceptableVariables(): array
    {
        return [
            self::NEW_LINE
        ];
    }

    public function getVariableValue(string $variable, LabelInterface $label, ProductInterface $product): string
    {
        switch ($variable) {
            case self::NEW_LINE:
                return '<br/>';
            default:
                throw new TextRenderException(__('The passed variable could not be processed'));
        }
    }
}
