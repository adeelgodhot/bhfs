<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Parts;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MetaProvider
     */
    private $metaProvider;

    public function __construct(
        ObjectManagerInterface $objectManager,
        MetaProvider $metaProvider
    ) {
        $this->objectManager = $objectManager;
        $this->metaProvider = $metaProvider;
    }

    /**
     * @param string $partCode
     * @return object
     */
    public function createPart(string $partCode)
    {
        try {
            $interface = $this->metaProvider->getInterface($partCode);

            return $this->objectManager->create($interface);
        } catch (\RuntimeException $e) {
            throw new \InvalidArgumentException(
                __('Label meta provider configuration error or invalid provided part code')->render()
            );
        }
    }
}
