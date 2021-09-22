<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */


declare(strict_types=1);

namespace Amasty\Paction\Plugin\Ui\Model;

use Magento\Ui\Config\Reader as ConfigReader;

class Reader extends AbstractReader
{
    public function afterRead(ConfigReader $subject, array $result): array
    {
        return $this->addMassactions($result);
    }
}
