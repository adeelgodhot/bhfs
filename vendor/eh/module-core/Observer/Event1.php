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
use EH\Core\Model\FeedFactory;

/**
 * Class Event1
 * @package EH\Core\Observer
 */
class Event1 implements ObserverInterface
{
    /**
     * @var BackendAuthSession
     */
    protected $_backendAuthSession;

    /**
     * @var FeedFactory
     */
    private $_feedFactory;

    /**
     * @param BackendAuthSession $backendAuthSession
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        BackendAuthSession $backendAuthSession,
        FeedFactory $feedFactory
    ) {
        $this->_feedFactory = $feedFactory;
        $this->_backendAuthSession = $backendAuthSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->_backendAuthSession->isLoggedIn()) {
            $feedModel = $this->_feedFactory->create();
            /* @var $feedModel \EH\Core\Model\Feed */
            $feedModel->checkUpdate();
        }
    }
}
