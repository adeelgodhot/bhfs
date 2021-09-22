<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Controller\Recipe;

class ListView extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Mageside\Recipe\Model\WriterFactory
     */
    protected $_writerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public $currentStoreId;

    /**
     * ListView constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Mageside\Recipe\Model\WriterFactory $writerFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Mageside\Recipe\Model\WriterFactory $writerFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_writerFactory = $writerFactory;
        $this->_coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($writerUrlKey = $this->getRequest()->getParam('writer')) {
            $writer = $this->_writerFactory->create()
                ->load($writerUrlKey, 'writer_url_key');
            if ($writer->getCustomerId()) {
                $this->_coreRegistry->register('writer', $writer);
            }
        }

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

        if ($this->getRequest()->isAjax()) {
            $content = $resultPage->getLayout()->getBlock('recipe_list')->toHtml();
            return $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData(['recipes' => $content]);
        }

        return $resultPage;
    }
}
