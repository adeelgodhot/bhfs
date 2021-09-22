<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */


declare(strict_types=1);

namespace Amasty\Paction\Plugin\Ui\Model;

use Magento\Ui\Model\Manager as UiManager;

class Manager extends AbstractReader
{
    public function afterGetData(UiManager $subject, array $result): array
    {
        return $this->addMassactions($result);
    }
}
