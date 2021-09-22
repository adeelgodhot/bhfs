<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AbstractArray
 */
abstract class AbstractArray extends AbstractSource implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $optionArray = [];
        $arr = $this->toArray();

        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $optionArray;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options = $this->toOptionArray();
        }

        return $this->_options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    abstract public function toArray(): array;
}
