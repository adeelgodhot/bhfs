<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model\Config\Backend;

class RoundRecoveryTime extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $recoveryTime = round($this->getValue(), 2);
        $this->setValue($recoveryTime);

        return $this;
    }
}
