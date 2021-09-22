<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Queue\Combination\Context;

use Magento\Framework\App\Http\Context;

interface CombinationSourceInterface
{
    public function getVariations(): array;

    public function getCombinationKey(): string;

    public function modifyRequest(array $combination, array &$requestParams, Context $context);

    public function prepareLog(array $crawlerLogData, array $combination): array;
}
