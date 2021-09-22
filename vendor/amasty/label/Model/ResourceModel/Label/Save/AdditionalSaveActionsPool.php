<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label\Save;

use ArrayIterator;
use IteratorAggregate as IteratorAggregate;

class AdditionalSaveActionsPool implements IteratorAggregate
{
    const SORT_ORDER = 'sortOrder';
    const ACTION = 'action';

    /**
     * @var array[]
     *
     * @example [
     *      [
     *          'sortOrder' => 12,
     *          'actions' => $action
     *      ]
     * ]
     */
    private $saveActions;

    public function __construct(
        $saveActions = []
    ) {
        $this->saveActions = $this->sortActions($saveActions);
    }

    private function sortActions($actionConfig): array
    {
        usort($actionConfig, function (array $configA, array $configB) {
            $sortOrderA = $configA[self::SORT_ORDER] ?? 0;
            $sortOrderB = $configB[self::SORT_ORDER] ?? 0;

            return $sortOrderA <=> $sortOrderB;
        });

        return $actionConfig;
    }

    public function getIterator(): iterable
    {
        $actions = [];

        foreach ($this->saveActions as $actionConfig) {
            $action = $actionConfig[self::ACTION] ?? null;

            if ($action instanceof AdditionalSaveActionInterface) {
                $actions[] = $action;
            }
        }

        return new ArrayIterator($actions);
    }
}
