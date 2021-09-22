<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Rule;

use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function create(string $type, array $data = []): RuleInterface
    {
        $rule = $this->objectManager->create($type, $data);

        if (false === $rule instanceof RuleInterface) {
            throw new \InvalidArgumentException(
                __('Object must be an instance of %1', RuleInterface::class)->render()
            );
        }

        return $rule;
    }
}
