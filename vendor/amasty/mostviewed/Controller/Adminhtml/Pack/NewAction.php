<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\Pack;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;

class NewAction extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(DataPersistorInterface $dataPersistor, Context $context)
    {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->dataPersistor->clear(Pack::PERSISTENT_NAME);
        $this->_forward('edit');
    }
}
