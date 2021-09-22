<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Observer;

use Magento\Backend\Model\Auth\Session as BackendAuthSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use EH\Core\Model\FeedFactory;
use EH\Core\Model\Processor;

/**
 * Class Event2
 * @package EH\Core\Observer
 */
class Event2 implements ObserverInterface
{
    /**
     * @var BackendAuthSession
     */
    protected $_backendAuthSession;

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Event2 constructor.
     * @param BackendAuthSession $backendAuthSession
     * @param ModuleListInterface $moduleList
     * @param Processor $processor
     * @param Registry $registry
     */
    public function __construct(
        BackendAuthSession $backendAuthSession,
        ModuleListInterface $moduleList,
        Processor $processor,
        Registry $registry
    ) {
        $this->moduleList = $moduleList;
        $this->registry = $registry;
        $this->processor = $processor;
        $this->_backendAuthSession = $backendAuthSession;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getControllerAction()->getRequest();
        $actionName = $request->getFullActionName();
        if (
            !$request->isAjax() &&
            $actionName != "extensionhut_action_validate" &&
            $this->_backendAuthSession->isLoggedIn() &&
            ($this->processor->cF() || $this->processor->cHRF())
        ) {
            $extensionNames = $this->moduleList->getNames();
            $ourExtensions = $this->processor->filterExtensions($extensionNames);
            foreach ($ourExtensions as $extensionName) {
                if (!$this->registry->registry($extensionName . '_l_message')) {
                    $this->registry->register($extensionName . '_l_message', 1);
                    $this->processor->getExtensionVersion($extensionName, true);
                }
            }
        }
    }
}
