<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Cron;

use Magento\Framework\Module\ModuleListInterface;
use EH\Core\Model\FeedFactory;
use EH\Core\Model\Processor;

/**
 * Class GetUpdates
 * @package EH\Core\Cron
 */
class GetUpdates
{
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * GetUpdates constructor.
     * @param ModuleListInterface $moduleList
     * @param Processor $processor
     */
    public function __construct(
        ModuleListInterface $moduleList,
        Processor $processor
    ) {
        $this->moduleList = $moduleList;
        $this->processor = $processor;
    }
    public function execute($force = false)
    {
        if ($force || $this->processor->canRun()) {
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->processor->filterExtensions($extensionNames);
            $this->processor->prepareExtensionVersions($ourExtensions);
        }
    }
}
