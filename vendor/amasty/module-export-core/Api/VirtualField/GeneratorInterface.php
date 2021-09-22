<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api\VirtualField;

interface GeneratorInterface
{
    public function generateValue(array $currentRecord): \Amasty\ExportCore\Api\VirtualField\GeneratorInterface;
}
