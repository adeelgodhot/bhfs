<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Block\Frontend;

class Index extends \Mageside\Recipe\Block\Frontend\AbstractBlock
{
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Writer\Collection
     */
    protected $_writer;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /** @var  */
    public $currentStoreId;

    /**
     * Index constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageside\Recipe\Model\FileUploader $fileUploader
     * @param \Mageside\Recipe\Helper\Config $helper
     * @param \Mageside\Recipe\Model\ResourceModel\Writer\Collection $writer
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageside\Recipe\Model\FileUploader $fileUploader,
        \Mageside\Recipe\Helper\Config $helper,
        \Mageside\Recipe\Model\ResourceModel\Writer\Collection $writer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_writer = $writer;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $fileUploader, $helper);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__($this->_helper->getSeoTitle()));

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'recipe_list',
                ['label' => __($this->_helper->getSeoTitle())]
            );
        }

        $this->currentStoreId = $this->_storeManager->getStore()->getId();
        return parent::_prepareLayout();
    }

    /**
     * @return $this
     */
    public function getWritersCollection()
    {
        $collectionCustomer = $this->_writer->addWriterFilter();

        if (count($collectionCustomer)) {
            $this->_coreRegistry->register('writers', $collectionCustomer);
        }

        return $collectionCustomer;
    }
}
