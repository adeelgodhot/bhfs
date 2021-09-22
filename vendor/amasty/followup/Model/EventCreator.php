<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

class EventCreator
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $events;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $events = []
    ) {
        $this->objectManager = $objectManager;
        $this->events = $events;
    }

    /**
     * @param string $type
     *
     * @param array $args
     *
     * @return \Amasty\Followup\Model\Event\Basic
     */
    public function create($type, $args = [])
    {
        return $this->objectManager->create($this->events[$type], $args);
    }
}
