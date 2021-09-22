<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Controller\Adminhtml\System\Message;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use EH\Core\Model\Processor;

/**
 * Class MarkRead
 * @package EH\Core\Controller\Adminhtml\System\Message
 */
class MarkRead extends AbstractAction
{
    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * MarkRead constructor.
     * @param Action\Context $context
     * @param WriterInterface $configWriter
     * @param CacheManager $manager
     */
    public function __construct(
        Action\Context $context,
        WriterInterface $configWriter,
        CacheManager $manager
    ) {
        $this->configWriter = $configWriter;
        $this->cacheManager = $manager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $extension = $this->getRequest()->getParam('extension');
        $version = $this->getRequest()->getParam('version');
        if ($extension && $version) {
            $this->configWriter->save(Processor::XML_BASE_CONFIG_PATH . $extension, $version);
            $this->cacheManager->clean(['config']);
            $this->messageManager->addSuccessMessage("Successfully Marked as Read.");
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
