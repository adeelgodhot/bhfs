<?php

/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer;

/**
 * Class Link
 * @package Wyomind\DataFeedManager\Block\Adminhtml\Feeds\Renderer
 */
class Link extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(\Wyomind\DataFeedManager\Helper\Delegate $wyomind, \Magento\Backend\Block\Context $context, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        parent::__construct($context, $data);
    }
    /**
     * Renders grid column
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $fileName = preg_replace('/^\\//', '', $row->getPath() . ($row->getPath() == "/" ? "" : "/") . $this->_dataHelper->getFinalFilename($row->getDateformat(), $row->getName(), $row->getUpdatedAt())) . $this->_dataHelper->getExtFromType($row->getType());
        $this->_storeManager->setCurrentStore($row->getStoreId());
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false);
        $url = $baseUrl . $fileName;
        $url = preg_replace('/([^\\:])\\/\\//', '$1/', $url);
        $url = str_replace('/pub/', '/', $url);
        $rootdir = $this->_list->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        if ($this->_io->fileExists($rootdir . '/' . $fileName)) {
            return '<a href="' . $url . '?r=' . time() . '" target="_blank">' . $url . '</a>';
        } else {
            return $url;
        }
    }
}