<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue\Combination;

use Amasty\Fpc\Model\Queue\Combination\Context\CombinationSourceInterface;

class Combinator
{
    /**
     * @param array $combination
     * @param CombinationSourceInterface $combinationSource
     * @return array
     */
    public function execute(array $combination, $combinationSource)
    {
        $result = [];
        $variations = $combinationSource->getVariations();
        $combinationKey = $combinationSource->getCombinationKey();

        if (!$variations) {
            return $combination;
        }

        foreach ($combination as $combinationUnit) {
            foreach ($variations as $variation) {
                $combinationUnit[$combinationKey] = $variation;
                $result[] = $combinationUnit;
            }
        }

        return $result;
    }
}
