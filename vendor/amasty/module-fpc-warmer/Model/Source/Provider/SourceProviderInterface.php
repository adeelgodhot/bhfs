<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Source\Provider;

interface SourceProviderInterface
{
    public function getPagesBySourceType(int $sourceType, int $pagesLimit): array;
}
