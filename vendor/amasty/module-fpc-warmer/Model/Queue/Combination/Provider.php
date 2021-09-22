<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue\Combination;

class Provider
{
    /**
     * @var Combinator
     */
    private $combinator;

    /**
     * @var array
     */
    private $combinationSources;

    private $combinations = null;

    public function __construct(
        Combinator $combinator,
        array $combinationSources = []
    ) {
        $this->combinator = $combinator;
        $this->combinationSources = $this->buildCombinationSources($combinationSources);
    }

    public function getCombinations(): array
    {
        if ($this->combinations === null) {
            $this->combinations = $this->buildCombinations();
        }

        return $this->combinations;
    }

    public function getCombinationSources(): array
    {
        return $this->combinationSources;
    }

    private function buildCombinations(): array
    {
        $combinations = [[]];

        foreach ($this->getCombinationSources() as $combinationSource) {
            $combinations = $this->combinator->execute($combinations, $combinationSource);
        }

        return $combinations;
    }

    private function buildCombinationSources(array $combinationSources): array
    {
        if (empty($combinationSources)) {
            return [];
        }

        $sources = [];
        foreach ($combinationSources as $combinationCode => $combinationProcessor) {
            /**
             * Backward compatibility with old sources implementation without sortOrder parameter
             * TODO: Remove it in next major version
             */
            if (is_object($combinationProcessor)) {
                $combinationProcessor = [
                    'sortOrder' => 0,
                    'processor' => $combinationProcessor,
                ];
            }

            if (!isset($combinationProcessor['sortOrder'])) {
                new \LogicException(
                    '"sortOrder" is not specified for combination processor "' . $combinationCode . '"'
                );
            }

            $sortOrder = (int)$combinationProcessor['sortOrder'];
            if (!isset($sources[$sortOrder])) {
                $sources[$sortOrder] = [];
            }
            $sources[$sortOrder][$combinationCode] = $combinationProcessor['processor'];
        }

        if (empty($sources)) {
            return [];
        }

        ksort($sources);

        return array_merge(...$sources);
    }
}
