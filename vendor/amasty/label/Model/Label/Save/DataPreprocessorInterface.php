<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save;

interface DataPreprocessorInterface
{
    /**
     * Prepare and validate data before save
     *
     * @param array $data
     * @return array
     */
    public function process(array $data): array;
}
