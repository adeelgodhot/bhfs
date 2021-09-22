<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Block\Frontend;

class AbstractBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mageside\Recipe\Model\FileUploader
     */
    protected $_fileUploader;

    /**
     * @var \Mageside\Recipe\Helper\Config
     */
    protected $_helper;

    /**
     * AbstractBlock constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageside\Recipe\Model\FileUploader $fileUploader
     * @param \Mageside\Recipe\Helper\Config $helper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageside\Recipe\Model\FileUploader $fileUploader,
        \Mageside\Recipe\Helper\Config $helper,
        array $data = []
    ) {
        $this->_fileUploader = $fileUploader;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $imagePath = $this->_fileUploader->getBaseUrl() . $this->_fileUploader->getBasePath();

        return $imagePath;
    }

    /**
     * @return string
     */
    public function getRecipePath()
    {
        return $this->_fileUploader->getBasePath();
    }

    /**
     * @param $string
     * @return int|mixed
     */
    public function getFormatServingsNumber($string)
    {
        $servingsNumber = array_unique(explode('-', $string));

        if (isset($servingsNumber[0]) && count($servingsNumber) == 1 && $servingsNumber[0] != 0) {
            return $servingsNumber[0];
        } elseif (!in_array('0', $servingsNumber) && ($servingsNumber[0] != 0)) {
            return str_replace('-', ' - ', $string);
        } elseif (isset($servingsNumber[0]) && isset($servingsNumber[1]) && ($servingsNumber[0] != 0 || $servingsNumber[1] != 0 )) {
            return $servingsNumber[0] ? $servingsNumber[1] : $servingsNumber[0];
        } else {
            return 1;
        }
    }

    /**
     * @param $time
     * @return bool|mixed
     */
    public function getFormatCookTime($time)
    {
        if ($time) {
            return str_replace(':', $this->escapeHtml('h').' ', $time);
        }

        return false;
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('recipe/recipe/listView');
    }

    /**
     * @return string
     */
    public function getImageStub()
    {
        return $this->getImageUrl() . DIRECTORY_SEPARATOR . \Mageside\Recipe\Helper\Image::PICTURE_STUB;
    }
}
