<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Model\Config\Backend;

class Blocks extends \Magento\Framework\App\Config\Value implements
    \Magento\Framework\App\Config\Data\ProcessorInterface
{

    /**
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $this->setValue(trim(implode(',', $this->getValue()), ','));

        return $this;
    }

    /**
     * Process config value
     *
     * @param string $value
     *
     * @return string
     */
    public function processValue($value)
    {
        return $value;
    }
}
