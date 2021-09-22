<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model;

use Magento\Backend\Model\Url;

class CommandResolver
{
    /**
     * @var array
     */
    protected $commands = [];

    /**
     * @var string
     */
    protected $actionUrl;

    public function __construct(
        Url $urlBuilder,
        array $commands = []
    ) {
        $this->actionUrl = $urlBuilder->getUrl('amasty_paction/massaction/index');
        $this->commands = $commands;
    }

    public function getCommand(string $name): ?Command
    {
        return $this->commands[$name] ?? null;
    }

    public function getCommandDataByName(string $name): array
    {
        /* initialization for delimiter lines*/
        $data = [
            'confirm_title' => '',
            'confirm_message' => '',
            'type' => $name,
            'label' => '------------',
            'url' => '',
            'fieldLabel' => ''
        ];

        if ($command = $this->getCommand($name)) {
            $data = $command->getCreationData();
            $data['url'] = $this->actionUrl;
        }

        return $data;
    }
}
